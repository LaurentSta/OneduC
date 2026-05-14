# 05 — Modules, SCORM & Quiz

## Hiérarchie du contenu pédagogique

```
Module
 └── ModuleSection (chapitre)
      └── ModuleLecture (leçon)
           ├── Contenu SCORM (package + version)
           ├── Contenu slides
           ├── Quiz natif (QuizQuestion + options)
           ├── Contenu vidéo / URL
           ├── LectureObjective (objectifs pédagogiques)
           └── LessonResource (ressources téléchargeables)
```

---

## Modules

### Modèle `Module`

Fichier : `app/Models/Module.php`

Champs principaux :
- `titre` / `nom` / `slug` / `description` / `objectifs`
- `category_id` / `subcategory_id`
- `formateur_id` (formateur associé au module)
- `status` (actif/inactif)
- `certificat` (champ présent, flux non implémenté)
- `bestseller`, `vedette`, `surevalue` (flags marketing hérités du template)
- `evaluation_id` (évaluation SCORM associée)

### Durée estimée intelligente

Le modèle calcule une durée pédagogique via :
- `getTotalSecondsAttribute()` — durée brute de toutes les leçons
- `getEstimatedSecondsForUser(int $userId)` — ajuste selon le nombre de tentatives passées du stagiaire

### Création des modules

La création est réservée à l'**administrateur** via `Backend/ModuleController`. Les formateurs assemblent des modules existants dans leurs groupes et parcours.

---

## Sections et Leçons

### Types de contenu d'une leçon

| Type | Champ | Description |
|------|-------|-------------|
| SCORM | `scorm_path`, `ScormPackageVersion` | Package SCORM importé avec versioning |
| Slides | `slides_path`, `slides_status` | Présentation convertie et hébergée |
| Quiz natif | `has_quiz` | Questions et réponses gérées nativement |
| Quiz live | `has_live_quiz` | Activé lors d'une session live formateur |
| Vidéo / URL | `content_type`, `content_url` | Lien externe ou vidéo intégrée |

### Personnalisation par groupe

La table `group_module_lectures` permet à chaque formateur de :
- **Masquer** une leçon pour son groupe
- **Réordonner** les leçons d'un module pour un groupe

Géré par `Formateur/GroupeModuleLessonController`.

---

## SCORM

### Import et versioning

Fichier : `app/Services/Scorm/ScormImporter.php`

Le processus d'import :
1. Validation du ZIP (taille max 500 Mo, format valide)
2. Extraction sécurisée dans `release_YYYYMMDD_HHMMSS_{random}` (protection path traversal via `safeExtract()`)
3. Détection du point d'entrée (`imsmanifest.xml` ou `index.html` fallback)
4. **Injection automatique** de `/scorm_core/js/API.js` dans la page d'entrée
5. Création/mise à jour d'un `ScormPackage` (slug stable) et d'une `ScormPackageVersion`

Le `ScormPackageVersion` expose un `getScormCacheTokenAttribute()` basé sur `imported_at` — cela force le navigateur à recharger l'API.js après chaque réimport, évitant les problèmes de cache.

### Runtime SCORM côté navigateur

| Fichier | Standard | Comportement |
|---------|----------|--------------|
| `public/scorm_core/js/API.js` | SCORM 1.2 | Expose `window.API`, envoie toutes les paires clé/valeur via `fetch()` vers `/scorm/save-progress` |
| `public/scorm_core/js/api_Scorm2004.js` | SCORM 2004 | Expose `window.API_1484_11`, stocke en mémoire, **ne poste pas** les interactions au backend |

### Routes SCORM

| Route | Méthode | CSRF | Authentification |
|-------|---------|------|-----------------|
| `/scorm/save-progress` | POST | Désactivé | Session Laravel (`Auth::id()`) |
| `/scorm/progress` | POST | Activé | `auth` middleware |
| `/lecture/{id}/scorm` | GET | — | Publique |
| `/scorm/evaluation-progress` | POST | Désactivé | Session Laravel |

### Données persistées

**Pour les leçons :**

| Table | Contenu |
|-------|---------|
| `scorm_results` | Log brut de toutes les paires clé/valeur envoyées |
| `scorm_scores` | `first_score`, `best_score`, `last_score`, `lesson_status`, `is_completed`, `session_time`, `attempts_count` |
| `scorm_interactions` | Questions/réponses SCORM — **non alimenté pour les leçons** (bug connu) |

**Pour les évaluations :**

| Table | Contenu |
|-------|---------|
| `scorm_evaluation_results` | Log brut évaluation |
| `scorm_evaluation_scores` | Scores évaluation |
| `scorm_evaluation_interactions` | Questions/réponses évaluation — **alimenté** via `EvaluationSCORMController` |

### Règles de complétion SCORM

| Contexte | Seuil de réussite | Configurable |
|----------|------------------|--------------|
| Leçons (`SCORMController`) | Score >= 50% | Non |
| Évaluations (`EvaluationSCORMController`) | Score >= 75% | Non |

Le statut est **monotone** : une leçon une fois marquée `completed` ou `passed` ne peut pas rétrograder.

### Limites SCORM connues

- `scorm_interactions` vide pour les leçons → les métriques de questions/réponses dans les dashboards sont structurellement vides pour le contenu SCORM
- `last_session_time` utilisé dans `SCORMController` mais pas migré → le cumul du temps de session est défaillant
- `attempts_count` initialisé à 1 et jamais incrémenté → toujours affiché à 1
- SCORM 2004 : les interactions ne sont pas postées au backend

---

## Quiz natifs

### Architecture

Les quiz natifs sont construits sur quatre modèles :

| Modèle | Table | Rôle |
|--------|-------|------|
| `QuizQuestion` | `quiz_questions` | Question avec type, médias, objectifs |
| `QuizOption` | `quiz_options` | Options de réponse |
| `QuizAttempt` | `quiz_attempts` | Tentative d'un utilisateur sur un quiz |
| `QuizAttemptQuestion` | `quiz_attempt_questions` | Réponse par question avec temps et résultat |

### Types de questions supportés

- Choix unique
- Choix multiple
- Vrai/Faux
- Texte libre
- Cloze (texte à trous)
- Médias (image, audio) sur la question et les options
- `image_alt` et `audio_transcript` pour l'accessibilité

### Import par CSV

La banque de questions admin (`Backend/QuizQuestionController`) supporte un import par fichier CSV pour créer des questions en masse.

### Scoring

Géré par `QuizService` :
- Score par question et score global
- Temps par question (`quiz_attempt_questions.time_spent_seconds`)
- Historique des tentatives avec approche "meilleure tentative" valorisée
- Seuil de réussite configurable par module

### Forces des quiz natifs par rapport au SCORM

Contrairement au SCORM, les quiz natifs alimentent complètement les tableaux de bord :
- Réponses détaillées par question
- Temps de réflexion moyen
- Identification des questions les plus échouées
- Distinction première tentative / meilleure tentative

---

## Sources de progression

La progression d'un stagiaire est multi-source, chaque format ayant sa propre table :

```
Quiz natif   → quiz_attempts + quiz_attempt_questions   (fiable, complet)
SCORM 1.2    → scorm_scores (scores, statut, temps)      (partiel — interactions vides)
SCORM 2004   → scorm_scores (scores, statut)             (très partiel — temps et interactions vides)
Vidéo        → video_segment_trackings                   (segmenté)
Manuel       → progressions                              (validation manuelle, sans preuve pédagogique)
```

Le service `LearningAnalyticsService` agrège toutes ces sources en un snapshot unifié par paire `(user_id, lecture_id)`.

---

[Retour au wiki](README.md)
