# 06 — Groupes & Parcours

*Public : formateurs et administrateurs pour la première partie ; la partie technique en fin de page s'adresse aux développeurs.*

## Le groupe, unité centrale

Un groupe relie tout ce qui compose une session de formation : des stagiaires, un formateur principal, d'éventuels co-formateurs et observateurs, des modules dans un ordre choisi, et éventuellement un parcours.

Un même utilisateur peut avoir des rôles différents selon les groupes : formateur dans l'un, observateur dans l'autre.

---

## Gérer un groupe (côté formateur)

### Créer un groupe

Deux champs obligatoires : le nom du groupe et les modules à affecter. Un mot de passe temporaire de groupe peut être défini ; chaque stagiaire reçoit par ailleurs son propre code d'accès de 6 caractères.

### Ajouter des stagiaires

Deux cas :
- le stagiaire a déjà un compte : rattachement direct par email ;
- le stagiaire est nouveau : le formateur saisit nom, prénom et email, le compte est créé et un mail d'invitation part avec le code d'accès et les instructions de première connexion. À sa première connexion, le stagiaire choisit son mot de passe.

### Ajouter un co-formateur

Depuis l'interface du groupe. Le co-formateur reçoit une notification et obtient les mêmes droits d'animation que le formateur principal sur ce groupe.

### Adapter les leçons pour un groupe

Pour chaque groupe, le formateur peut masquer une leçon (sans la supprimer du module) ou réordonner les leçons dans l'affichage stagiaire. Utile quand un même module sert à des groupes de niveaux différents.

### Affecter des modules

Un module peut être affecté à plusieurs groupes en même temps, avec un ordre d'affichage et un statut d'activation propres à chaque groupe.

---

## Gérer un groupe (côté administrateur)

L'espace administrateur propose un CRUD groupe distinct de l'interface formateur. La liste dense affiche le formateur principal, son statut, le nombre de stagiaires et la dernière mise à jour. À la création ou à la modification, l'administrateur renseigne un nom unique, une description optionnelle, choisit obligatoirement le formateur principal et sélectionne les stagiaires à rattacher.

Le serveur vérifie que le formateur sélectionné existe, n'est pas supprimé logiquement et possède bien le rôle `formateur`. Chaque membre sélectionné doit de même exister, ne pas être supprimé logiquement et posséder le rôle `stagiaire`. La création ou la modification du groupe et la synchronisation de ses membres s'exécutent dans une même transaction. Les rattachements stagiaires sont enregistrés avec `group_user.role_in_group = 'stagiaire'` ; les rattachements d'un autre rôle ne sont pas convertis silencieusement.

La gestion unifiée des utilisateurs permet en parallèle d'affecter un stagiaire à un ou plusieurs groupes et de définir son formateur principal. Si aucun formateur n'est choisi, le formateur principal du premier groupe sélectionné est repris dans `users.formateur_id`. Un code stagiaire unique de six caractères est généré lorsqu'aucun code n'est saisi.

> **Avertissement :** la suppression d'un groupe depuis l'administration est immédiate et physique. La confirmation affichée dans l'interface évite une action involontaire, mais ne fournit ni corbeille ni restauration. Il faut vérifier les membres, modules et données de progression associés avant de confirmer.

---

## Les parcours

Convention de vocabulaire : côté formateur on parle de **parcours** ; côté stagiaire le même ensemble s'appelle **ma formation** ou **mon programme**. Le terme **module** reste réservé aux briques pédagogiques.

Deux notions coexistent, et il ne faut pas les confondre :

**Le parcours formateur Oneduc** est l'onboarding du formateur sur la plateforme elle-même. Il guide la prise en main d'Oneduc : 5 modules (2 développés), accessibles via `/formateur/parcours-formateur`. Ce n'est pas un parcours pour les stagiaires.

**Les parcours créés par le formateur** sont les parcours pédagogiques qu'un formateur assemble pour ses groupes : une séquence ordonnée de modules, nuages de mots et sondages. Un parcours peut être associé à un groupe ; les modules sont alors présentés au stagiaire dans l'ordre du parcours.

### Ce que les parcours ne font pas encore

- Pas de prérequis bloquants : un stagiaire peut accéder aux modules dans n'importe quel ordre.
- Pas de validation de compétences ni de jalons obligatoires.
- Pas de certificat en fin de parcours.

---

## Partie technique

### Modèle `Group`

Fichier : `app/Models/Group.php`

Champs principaux : `name`, `description`, `is_active`, `is_sandbox`, `start_date`, `end_date`, `instructor_id` (formateur principal), `temporary_password` (cast `encrypted` — jamais accessible en clair hors application), `formateur_parcours_id` (parcours associé, optionnel).

Le code court de connexion stagiaire est porté par `users.code_acces`. Dans la gestion admin unifiée, `CodeGeneratorService` génère un code unique de six caractères lorsqu'aucun code n'est fourni à la création ou à la modification du stagiaire.

### Scope formateur

```php
Group::scopeAccessibleByTrainer(Builder $query, User $trainer): Builder
```

Couvre les deux façons d'être formateur d'un groupe : être le `instructor_id`, ou co-formateur via `group_user.role_in_group = 'formateur'`. À utiliser systématiquement dans les contrôleurs formateur pour ne pas oublier les co-formateurs.

### Pivots

`group_user` — le pivot central des relations groupe-utilisateur :

| Colonne | Valeurs | Rôle |
|---------|---------|------|
| `group_id` | ID du groupe | Référence le groupe |
| `user_id` | ID de l'utilisateur | Référence l'utilisateur |
| `role_in_group` | `stagiaire`, `formateur`, `observateur` | Rôle dans ce groupe |

`group_module` — les modules affectés à chaque groupe :

| Colonne | Rôle |
|---------|------|
| `group_id` | Groupe concerné |
| `module_id` | Module affecté |
| `position` | Ordre d'affichage dans la liste stagiaire |
| `is_active` | Activation du module pour le groupe |

La personnalisation des leçons par groupe passe par `group_module_lectures`, gérée par `Formateur/GroupeModuleLessonController`.

### Modèles des parcours

- Parcours formateur Oneduc : contenu codé en dur dans `app/Data/ParcoursFormateur.php` (`ParcoursController`). Détail dans [docs/parcours-formateur.md](../parcours-formateur.md).
- Parcours créés par le formateur : `FormateurParcours` (en-tête) + `FormateurParcoursItem` (éléments ordonnés, types `module`, `wordcloud`, `poll`), gérés par `Formateur/MesFormationsController`. L'association au groupe passe par `groups.formateur_parcours_id` ; quand elle existe, `StagiaireModules()` suit l'ordre du parcours.

### Flux d'invitation stagiaire

1. Le formateur saisit nom, prénom, email dans l'interface groupe
2. Création du compte (`role = stagiaire`)
3. Création d'un `StagiaireGroupInvitation`
4. Envoi du mail d'invitation avec code d'accès et instructions
5. Changement de mot de passe forcé à la première connexion

### Limitations connues

- Un stagiaire dans plusieurs groupes actifs ne voit que les modules du premier groupe retourné (`.first()` dans `StagiaireController::StagiaireModules()`). Bug à corriger en phase 2.
- Le modèle `Group` utilise `SoftDeletes` depuis le 14 juillet 2026 : une suppression de groupe (admin ou formateur) est réversible en base et préserve les données de progression liées — voir [10-securite-rgpd.md](10-securite-rgpd.md). Aucune interface de restauration n'existe encore côté admin.
- La suppression d'un formateur déclenche la suppression physique de ses groupes. Les stagiaires sans autre formateur ni autre groupe principal peuvent alors être supprimés logiquement, avec purge immédiate d'une partie de leurs données liées.

---

[Retour au wiki](README.md)
