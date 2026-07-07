# 16 — Émargement (feuille de présence)

*Public : formateurs (usage) et développeurs (partie technique en fin de page).*

## Vue d'ensemble

L'émargement permet à un formateur de produire, pour chaque groupe, une **feuille de présence par séance datée** (matin, après-midi, journée complète ou soirée), signée individuellement par chaque stagiaire, et exportable en PDF pour un audit **Qualiopi/OPCO**.

C'est volontairement un document **administratif**, distinct des outils d'animation ludiques décrits en page [07](07-outils-animation.md) :
- l'accès stagiaire se fait par appartenance vérifiée au groupe, jamais par un simple code public partagé ;
- la preuve de présence est une **signature graphique** (pad de dessin), pas un pointage en un clic ;
- chaque séance correspond à un vrai créneau daté (impossible d'en recréer une identique par erreur pour le même groupe).

L'émargement est distinct de l'**assiduité plateforme** déjà suivie dans Progression (temps de connexion, jours actifs) — les deux indicateurs sont affichés côte à côte, sans être fusionnés, car ils ne mesurent pas la même chose.

## Utilisation côté formateur

L'émargement est accessible depuis deux endroits :
- le bouton **« Émargement »**, à côté de « Filtrer », sur la page **Mes stagiaires** ;
- la carte **« Émargement »** du tableau **Outils numériques**, avec un badge qui indique combien de séances sont actuellement ouvertes.

L'outil s'active **groupe par groupe** : par défaut, aucun groupe n'est concerné. Deux façons de l'activer :
- directement dans les **options du groupe**, à la création ou depuis sa modification (onglet « Informations »), à côté du bouton « Activer le groupe » ;
- ou depuis l'écran d'accueil de l'outil Émargement, où chaque groupe du formateur apparaît sous forme de carte : s'il n'est **pas encore activé**, un bouton « Activer pour ce groupe » suffit à démarrer ; s'il est **déjà activé**, la carte est cliquable et ouvre directement la gestion de ses séances (un lien permet de désactiver à tout moment, sans perdre l'historique des séances déjà créées).

Une fois un groupe activé, le formateur peut :
- **créer une séance** : une date, un créneau (matin, après-midi, journée complète ou soirée), et un titre facultatif ;
- consulter la **liste des séances** déjà créées, avec le nombre de stagiaires ayant signé pour chacune.

En cliquant sur **« Piloter »** pour une séance, le formateur arrive sur un écran de suivi en direct où il peut :
- **ouvrir la séance**, ce qui permet aux stagiaires de signer depuis leur propre espace ;
- voir en temps réel qui a signé, avec l'image de chaque signature ;
- **marquer un stagiaire absent** (avec un motif facultatif), ou **signer à sa place** s'il n'a pas de compte actif ou a oublié ;
- **clôturer la séance** une fois terminée — les stagiaires ne peuvent alors plus signer eux-mêmes, mais le formateur garde la main pour corriger si besoin ;
- **exporter la feuille en PDF** à tout moment, pour la conserver ou la fournir lors d'un contrôle Qualiopi/OPCO.

## Utilisation côté stagiaire

Quand une séance est ouverte pour son groupe, le stagiaire voit apparaître un bouton **« Signer maintenant »** sur la carte Émargement de son tableau **Outils**. Il dessine sa signature du doigt ou à la souris, puis valide. Une fois signée, seul le formateur peut encore modifier cette présence.

Une alerte apparaît également dans la **cloche de notification**, en haut de toutes les pages de son espace — pas besoin d'aller chercher la bonne page pour s'en rendre compte. Elle disparaît automatiquement dès que la signature est faite. Il n'y a pas encore d'alerte par email : la notification dans l'application a été jugée plus fiable pour ce public.

## Partie technique

### Schéma de données

```sql
seances (
  id, group_id, formateur_id,
  date, creneau ENUM(matin, apres_midi, journee, soiree),
  heure_debut, heure_fin, titre,
  statut ENUM(planifiee, ouverte, cloturee),
  opened_at, closed_at
)
-- unique (group_id, date, creneau)

seance_presences (
  id, seance_id, user_id,
  stagiaire_nom_snapshot,       -- figé à la création du roster
  statut ENUM(en_attente, present, absent),
  signature_type ENUM(auto, formateur),  -- qui a posé la signature
  motif_absence, updated_by
)
-- unique (seance_id, user_id)
```

Le roster (`seance_presences`) est **figé au moment de la création de la séance** à partir de `$group->students()` — pas recalculé dynamiquement — pour que la feuille reste fidèle à qui était présent ce jour-là même si la composition du groupe change ensuite.

L'activation par groupe est un simple booléen `groups.emargement_enabled` (défaut `false`, même famille que `is_active`/`is_sandbox`) — ce n'est qu'un filtre d'affichage sur `EmargementController::index()` : désactiver un groupe ne touche ni ne supprime ses séances existantes, ça masque juste l'accès à la gestion depuis la vue dédiée tant qu'il n'est pas réactivé. Deux points d'écriture pour ce même champ : `Formateur\GroupeController::store()`/`update()` (toggle dans les options du groupe, pattern identique à `is_active` — hidden input + checkbox) et `EmargementController::activerGroupe()`/`desactiverGroupe()` (depuis la vue dédiée). Validation `nullable|boolean` (pas `required` comme `is_active`) pour rester rétrocompatible avec les appels existants au formulaire groupe qui ne connaissent pas ce champ.

### Emplacement du code

| Élément | Emplacement |
|---|---|
| Modèles | `app/Models/Seance.php`, `app/Models/SeancePresence.php` |
| Logique métier (français, cf. convention CLAUDE.md) | `app/Domains/Outils/Emargement/Actions/` (`CreerSeance`, `OuvrirSeance`, `ClorerSeance`, `SignerPresence`, `CorrigerPresence`) et `Support/` (`AccesEmargement`, `SignatureImage`, `GenererPdfEmargement`) |
| Contrôleurs | `app/Http/Controllers/Formateur/EmargementController.php` (méthode `index()` = vue dédiée), `app/Http/Controllers/Stagiaire/EmargementController.php` (dont `notificationStatus()` pour la cloche) |
| Vues | `resources/views/formateur/emargement/` (`index.blade.php` = vue dédiée avec choix de groupe, `seances-panel.blade.php` = partial liste/création réutilisé par `index.blade.php`), `resources/views/stagiaire/emargement/` |

### Écarts volontaires par rapport au pattern des autres outils (page 07)

- **Pas d'`access_code`** : contrairement à Sondage/Quiz/Roue/Échelle (accès par code public), l'accès stagiaire réutilise le pattern Whiteboard/Timer — `$group->students()->where('users.id', auth()->id())->exists()` — car une signature légale doit être rattachée à un compte authentifié et vérifié membre du groupe, jamais à "qui a le code".
- **Pad de signature en canvas HTML5 natif** (`resources/views/components/oneduc/signature-pad.blade.php`, Pointer Events, `canvas.toDataURL('image/png')`), pas de dépendance JS externe — le tableau blanc utilise Excalidraw (React), largement surdimensionné pour un simple pad de signature.
- **Stockage de la signature via Spatie Media Library**, disque `local` (privé, jamais servi par une URL publique) — une signature manuscrite est une donnée sensible à valeur de preuve légale. L'image est toujours relue et embarquée en base64 côté serveur (JSON de polling, template PDF), jamais exposée par une route de fichier dédiée.
- **Export PDF** via `barryvdh/laravel-dompdf` (nouvelle dépendance — aucune génération PDF n'existait avant dans le projet). Le template (`formateur/emargement/pdf.blade.php`) utilise du CSS inline, pas de classes Tailwind (dompdf ne consomme pas le build Vite).
- **Alerte stagiaire par polling, pas par notification native Laravel** : `resources/views/components/user-notification-bell.blade.php` mélange déjà deux systèmes (notifications Eloquent natives + polling JS toutes les 5s pour Whiteboard/Quiz live/Mur de questions). L'émargement suit le second pattern — `Stagiaire\EmargementController::notificationStatus()` (route `stagiaire.emargement.notification-status`) renvoie `has_open_seance`/`group_name`/`opened_at_human`/`join_url`, consommé par un `updateEmargement()`/`pollEmargementStatus()` dédié dans le même fichier. La condition de disparition n'est pas juste "séance ouverte" mais "séance ouverte **et** ma `SeancePresence` est encore `en_attente`" — l'alerte s'éteint dès la signature, avant même la clôture de la séance.
- **Deux familles de routes formateur** : `formateur.emargement.index` (top-level, sans `{group}` — la vue dédiée avec choix/activation de groupe, + `formateur.emargement.activer`/`.desactiver` en `POST /formateur/emargement/groupes/{group}/...`) et `formateur.groupes.emargement.*` (scopées `{group}`, pour créer/piloter/corriger/exporter — inchangées depuis la conception initiale). `EmargementController::store()` redirige systématiquement vers `formateur.emargement.index` avec le `group_id` de la séance créée.
- **Activation par groupe, pas par défaut** : contrairement aux autres outils (toujours disponibles pour tout groupe), l'émargement est opt-in via `groups.emargement_enabled`. Ce n'est pas une contrainte de sécurité (les routes scopées `{group}` restent protégées par `AccesEmargement::assertFormateurAccess()` indépendamment de ce flag) — uniquement un filtre d'affichage sur la vue dédiée, pour que la liste de groupes n'impose pas l'outil à des formations qui n'en ont pas besoin.

### Historique du placement (pour archive)

Trois emplacements ont été essayés avant la version actuelle, tous le 2026-07-07 : (1) un 4ᵉ onglet de la fiche groupe, retiré car le formateur pense d'abord au stagiaire à faire signer, pas à l'administration du groupe ; (2) une section sur la fiche individuelle du stagiaire, remplacée pour que l'outil ait un point d'entrée unique plutôt que dispersé sur chaque fiche ; (3) la version actuelle — vue dédiée reliée depuis la liste des stagiaires, cohérente avec la façon dont Sondage/Quiz ont chacun leur propre page.

### Suite de tests

`tests/Feature/Emargement/` couvre la création de séance (roster, contrainte unique), l'ouverture/fermeture, la signature stagiaire (accès, refus si séance fermée ou déjà signée), la correction formateur (absent/signature déléguée), l'export PDF, et l'intégration dans Progression et les dashboards Outils.

---

[Retour au wiki](README.md)
