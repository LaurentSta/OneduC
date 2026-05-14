# 06 — Groupes & Parcours

## Groupes de formation

### Concept

Un groupe est l'unité organisationnelle centrale d'Oneduc. Il relie :
- Un **formateur principal** (`groups.instructor_id`)
- Des **stagiaires** (via `group_user` avec `role_in_group = 'stagiaire'`)
- Des **co-formateurs** (via `group_user` avec `role_in_group = 'formateur'`)
- Des **observateurs** (via `group_user` avec `role_in_group = 'observateur'`)
- Des **modules** ordonnés (via `group_module` avec `position`)
- Un **parcours de formation** optionnel (`groups.formateur_parcours_id`)

### Modèle `Group`

Fichier : `app/Models/Group.php`

Champs principaux :
- `name` / `description` / `status` (actif/inactif)
- `instructor_id` (formateur principal)
- `temporary_password` (code d'accès, stocké chiffré via le cast `encrypted`)
- `formateur_parcours_id` (parcours associé, optionnel)
- `access_code` (code d'accès court pour les stagiaires)

Le mot de passe temporaire de groupe est chiffré en base de données via le cast Laravel `encrypted` — les valeurs brutes ne sont jamais accessibles en clair hors de l'application.

### Scope formateur

```php
Group::scopeAccessibleByTrainer(Builder $query, User $trainer): Builder
```

Ce scope couvre les deux façons d'être formateur d'un groupe :
1. Être le `instructor_id` du groupe
2. Être co-formateur via `group_user.role_in_group = 'formateur'`

C'est le scope à utiliser systématiquement dans les contrôleurs formateur pour éviter d'oublier les co-formateurs.

---

## Gestion des groupes (côté formateur)

### Créer un groupe

Via `Formateur/GroupeController::store()`. Champs obligatoires : nom du groupe, modules à affecter.

Un code d'accès court est généré automatiquement via `CodeGeneratorService` (6 caractères alphanumériques).

### Ajouter des stagiaires

Deux modes :
- **Stagiaire existant** : rattachement direct par email
- **Nouveau stagiaire** : création du compte + invitation mail avec code d'accès

### Ajouter un co-formateur

Via l'interface de gestion du groupe. Le co-formateur reçoit une notification interne. Il accède au groupe via `scopeAccessibleByTrainer`.

### Personnalisation des leçons par groupe

Via `Formateur/GroupeModuleLessonController` et la table `group_module_lectures`.

Chaque formateur peut pour son groupe :
- **Masquer** une leçon (sans la supprimer du module)
- **Réordonner** les leçons dans l'affichage stagiaire

Cette fonctionnalité est avancée et rarement présente dans les LMS à ce stade de développement.

---

## Parcours de formation

### Deux notions coexistent

#### 1. Parcours formateur Oneduc (`ParcoursController`)

C'est le **parcours d'onboarding du formateur sur la plateforme elle-même**. Il guide le formateur dans la découverte d'Oneduc.

Structure : 5 modules (2 développés), chacun avec chapitres et leçons. Le contenu est codé en dur dans `app/Data/ParcoursFormateur.php` car ce parcours est intégré directement à la plateforme.

Accessible via `/formateur/parcours-formateur`.

Voir le détail dans [docs/parcours-formateur.md](../parcours-formateur.md).

#### 2. Formations créées par le formateur (`FormateurParcours`)

Ce sont les **parcours pédagogiques** qu'un formateur construit pour ses groupes de stagiaires.

Modèles :
- `FormateurParcours` — en-tête du parcours (titre, formateur)
- `FormateurParcoursItem` — éléments ordonnés (type : `module`, `wordcloud`, `poll`, `activity`)

Géré via `Formateur/MesFormationsController`.

Un parcours peut être **associé à un groupe** via `groups.formateur_parcours_id`. Quand c'est le cas, la vue `StagiaireModules()` présente les modules dans l'ordre défini par le parcours.

### Limites des parcours

- Pas de prérequis bloquants (un stagiaire peut accéder à n'importe quel module du parcours dans n'importe quel ordre)
- Pas de validation de compétences ou de jalons obligatoires
- Pas de remédiation automatique
- Pas de génération de certificat en fin de parcours

---

## Pivot `group_user`

La table `group_user` est le pivot central des relations groupe-utilisateur.

| Colonne | Valeurs possibles | Rôle |
|---------|------------------|------|
| `group_id` | ID du groupe | Référence le groupe |
| `user_id` | ID de l'utilisateur | Référence l'utilisateur |
| `role_in_group` | `stagiaire`, `formateur`, `observateur` | Rôle dans ce groupe |

Un même utilisateur peut avoir des rôles différents dans des groupes différents.

---

## Affectation des modules à un groupe

La table `group_module` gère les modules affectés à chaque groupe :

| Colonne | Rôle |
|---------|------|
| `group_id` | Groupe concerné |
| `module_id` | Module affecté |
| `position` | Ordre d'affichage dans la liste stagiaire |
| `is_active` | Activation du module pour le groupe |

Un module peut être affecté à plusieurs groupes simultanément, avec un ordre et un statut différents pour chaque groupe.

---

## Invitation stagiaire

Flux d'invitation d'un nouveau stagiaire :
1. Le formateur saisit le nom, prénom et email du stagiaire dans l'interface groupe
2. Le système crée un compte utilisateur (`role = stagiaire`)
3. Un `StagiaireGroupInvitation` est créé
4. Un mail d'invitation est envoyé avec les instructions de première connexion et le code d'accès du groupe
5. À la première connexion, le stagiaire est invité à changer son mot de passe

---

## Limitations connues

- Un stagiaire dans **plusieurs groupes actifs** ne voit que les modules du premier groupe retourné (`.first()` dans `StagiaireController::StagiaireModules()`). C'est un bug à corriger en phase 2.
- Le modèle `Group` n'a **pas de `SoftDeletes`** : une suppression de groupe est physique et peut laisser des données de progression orphelines (avec `group_id` invalide).

---

[Retour au wiki](README.md)
