# 08 — Tableaux de bord

## Vue d'ensemble

Chaque rôle dispose d'un tableau de bord adapté à ses besoins. La donnée est centralisée via `LearningAnalyticsService` pour le tableau de bord formateur, tandis que certains écrans stagiaires conservent encore des agrégations propres dans `StagiaireController`.

---

## Tableau de bord administrateur

**Fichiers :** `app/Http/Controllers/AdminController.php` → `resources/views/admin/index.blade.php`

### Indicateurs affichés

| Indicateur | Source |
|------------|--------|
| Nombre de catégories | `Category::count()` |
| Nombre de sous-catégories | `SubCategory::count()` |
| Nombre de modules | `Module::count()` |
| Nombre de formateurs | `User::where('role', 'formateur')` |
| Nombre de stagiaires | `User::where('role', 'stagiaire')` |
| Nombre de groupes | `Group::count()` |
| Nombre de sections | `ModuleSection::count()` |
| Nombre de leçons | `ModuleLecture::count()` |

### Caractéristiques

- **Volumétrique** : le dashboard admin est un tableau de pilotage système, pas un tableau d'analyse pédagogique
- Suffisant pour surveiller la croissance de la plateforme
- Insuffisant pour piloter la qualité ou la progression pédagogique

### Manques

- Pas de taux d'activité, de complétion moyenne, de stagiaires actifs/inactifs
- Pas d'alertes qualité contenu
- Pas d'indicateurs par organisme ou par groupe
- Les données SCORM calculées (`scormSummaries`) ne sont pas exploitées dans la vue actuelle

---

## Tableau de bord formateur

**Fichiers :** `app/Http/Controllers/FormateurController.php` (687 lignes) + `app/Services/LearningAnalyticsService.php` (502 lignes) → `resources/views/formateur/index.blade.php`

### Indicateurs affichés

| Bloc | Indicateurs | Source principale |
|------|-------------|-----------------|
| Groupes | Liste des groupes accessibles, nombre de stagiaires, statut | `Group::scopeAccessibleByTrainer()` |
| Vue globale | Taux de réussite moyen, stagiaires actifs/inactifs/non démarrés | `LearningAnalyticsService` |
| Priorités | Groupes prioritaires (le plus d'apprenants à risque) | Calcul sur snapshots |
| Priorités | Modules prioritaires (progression la plus faible) | Calcul sur snapshots |
| Activité | Graphique temporel AJAX (jour / semaine / mois / an) | Endpoint AJAX avec cache serveur |

### `LearningAnalyticsService::collectSnapshots()`

Cette méthode est le cœur analytique de la plateforme. Elle :

1. Charge toutes les données de progression pour les groupes du formateur :
   - `Progression` (leçons validées manuellement)
   - `ScormResult` et `ScormScore` (SCORM)
   - `ScormInteraction` (questions SCORM — vide pour les leçons)
   - `VideoSegmentTracking` (vidéo)
   - `QuizAttempt` et `QuizAttemptQuestion` (quiz natifs)

2. Produit un **snapshot unifié** par paire `(user_id, lecture_id)` via `finalizeSnapshot()`

3. `finalizeSnapshot()` détermine :
   - `is_started` — la leçon a-t-elle été ouverte ?
   - `is_successful` — la leçon a-t-elle été réussie ?
   - `last_activity_at` — dernière activité horodatée

### Fiabilité par type de contenu

| Type de contenu | Fiabilité dashboard |
|-----------------|---------------------|
| Quiz natifs | Excellente — toutes les réponses et temps sont enregistrés |
| SCORM (scores) | Bonne — scores et statuts enregistrés correctement |
| SCORM (questions) | Nulle — `scorm_interactions` vide pour les leçons ordinaires |
| Vidéo | Partielle — segments de lecture mais pas de compréhension |
| Manuel | Partielle — présence validée sans preuve pédagogique |

### Graphique d'activité AJAX

Le formateur peut filtrer l'activité par période (jour, semaine, mois, an). L'endpoint AJAX utilise un cache côté serveur pour éviter les requêtes répétées sur les grands groupes.

---

## Tableau de bord stagiaire

**Fichiers :** `app/Http/Controllers/StagiaireController.php` → `resources/views/stagiaire/index.blade.php`

### Indicateurs affichés

| Indicateur | Source |
|------------|--------|
| Formateur référent | Groupe actif du stagiaire |
| Temps d'apprentissage | `VideoSegmentTracking` + `ScormScore.session_time` + `QuizAttempt.total_time_seconds` |
| Questions traitées | `ScormInteraction` (vide pour SCORM) + `QuizAttemptQuestion` |
| Taux de réussite | Ratio questions réussies / questions traitées |
| Formations en cours | Modules du groupe actif avec progression par section |
| Progression par module | Ratio leçons terminées / leçons totales |
| Graphique de réussite | Scores par tentative |
| Temps moyen de réflexion | `quiz_attempt_questions.time_spent_seconds` moyen |

### Points forts

- Interface épurée, adaptée à un public non-expert
- Formateur référent toujours visible — lien humain valorisé
- Approche motivante : progression globale, pas seulement les échecs

### Limites

- Si le contenu est majoritairement SCORM, "questions traitées" et "taux de réussite" sont sous-estimés (interactions SCORM non enregistrées)
- Un stagiaire dans plusieurs groupes actifs ne voit que le premier groupe
- `StagiaireController` concentre encore dashboard, modules, résultats et outils ; un découpage améliorerait la maintenabilité

---

## Vues de progression formateur

En plus du tableau de bord principal, le formateur dispose de vues de progression détaillées :

| Vue | Contrôleur | Contenu |
|-----|-----------|---------|
| Par groupe | `ProgressionGroupesController` | Tous les stagiaires du groupe avec statut global |
| Par stagiaire | `ProgressionStagiairesController` | Vue consolidée multi-modules |
| Par module | `ProgressionModulesController` | Tous les stagiaires sur un module donné |
| Détail stagiaire | `ProgressionStagiaireController` | Leçon par leçon pour un stagiaire |

Le `ProgressionStagiaireController` (630 lignes) agrège les mêmes sources que `LearningAnalyticsService` mais avec un niveau de détail plus fin — à la question individuelle.

---

## Recommandations d'amélioration dashboard

Pour rendre les tableaux de bord plus fiables et exploitables :

1. **Afficher la source** des indicateurs (quiz / SCORM / vidéo / manuel) pour que le formateur sache d'où vient le chiffre
2. **Alerte SCORM incomplète** : signaler quand `scorm_interactions` est vide pour un contenu SCORM afin d'éviter des interprétations erronées
3. **Exporter** les données de progression (CSV par groupe, PDF fiche individuelle)
4. **Unifier les définitions** : "actif", "terminé", "réussi", "temps d'apprentissage" doivent avoir la même définition sur tous les écrans

---

## État de vérification

Au 3 juillet 2026 :
- `php artisan test` : 102 tests passés, 501 assertions ;
- `npm run build` : réussi, avec avertissements de bundles Vite volumineux.

---

[Retour au wiki](README.md)
