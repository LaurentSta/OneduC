# 16 — Émargement (feuille de présence)

*Public : formateurs (usage) et développeurs (partie technique en fin de page).*

## Vue d'ensemble

L'émargement permet à un formateur de produire, pour chaque groupe, une **feuille de présence par séance datée** (matin, après-midi, journée complète ou soirée), signée individuellement par chaque stagiaire, et exportable en PDF pour un audit **Qualiopi/OPCO**.

C'est volontairement un document **administratif**, distinct des outils d'animation ludiques décrits en page [07](07-outils-animation.md) :
- l'accès stagiaire se fait par appartenance vérifiée au groupe, jamais par un simple code public partagé ;
- la preuve de présence est une **signature graphique** (pad de dessin), pas un pointage en un clic ;
- il n'y a pas de notion de "session relançable" : une séance correspond à un vrai créneau calendaire, unique par groupe/date/créneau.

L'émargement est distinct de l'**assiduité plateforme** déjà suivie dans Progression (temps de connexion, jours actifs) — les deux indicateurs sont affichés côte à côte, sans être fusionnés, car ils ne mesurent pas la même chose.

## Utilisation côté formateur

L'émargement est un **outil à part entière**, avec sa propre vue dédiée (`/formateur/emargement`) :

- accessible via le bouton **« Émargement »** à côté de « Filtrer » sur `/formateur/stagiaires` (hérite du groupe actif dans le filtre de cette liste, s'il y en a un) ;
- accessible aussi depuis `/formateur/outils`, avec un badge indiquant le nombre de séances actuellement ouvertes.

L'émargement s'**active groupe par groupe** — aucun groupe n'a l'outil actif par défaut. La vue liste tous les groupes du formateur :
- un groupe **non activé** affiche juste un bouton « Activer pour ce groupe » ;
- un groupe **activé** est une carte cliquable (badge « Activé ») qui ouvre sa gestion de séances.

Une fois un groupe activé et sélectionné (`?group_id=`) :
- création d'une séance (date, créneau, titre optionnel) — une seule séance par couple (groupe, date, créneau) ;
- liste des séances existantes avec le nombre de signatures obtenues ;
- un lien discret « Désactiver l'émargement pour ce groupe » redonne la main sans perdre l'historique des séances déjà créées (elles restent en base, juste masquées de cette vue tant que le groupe n'est pas réactivé).

Bouton **« Piloter »** sur une séance → page dédiée (`/formateur/groupes/{group}/emargement/{seance}`) :
- **Ouvrir la séance** : à partir de là, les stagiaires du groupe peuvent signer depuis leur espace ;
- suivi en direct de qui a signé (rafraîchissement automatique toutes les 3 secondes), avec la signature affichée ;
- **Marquer absent** (avec motif optionnel) ou **Signer à sa place** (pour un stagiaire sans compte actif ou ayant oublié) ;
- **Clôturer la séance** : les stagiaires ne peuvent plus s'auto-signer, mais les corrections formateur restent possibles ;
- **Exporter en PDF** à tout moment (disponible aussi depuis la vue dédiée pour l'historique).

> **Historique du placement** : v1 (2026-07-07 matin) mettait la gestion dans un 4ᵉ onglet de la fiche groupe — rejeté sur retour terrain ("le workflow ne colle pas au terrain"). v2 (même jour, après-midi) déplaçait la création/liste sur la fiche individuelle du stagiaire. v3 en a fait un outil à part entière avec sa propre vue dédiée, reliée depuis la liste des stagiaires plutôt qu'une fiche stagiaire précise — cohérent avec la façon dont les autres outils (Sondage, Quiz...) ont chacun leur page dédiée. v4 (même jour) a ajouté l'activation par groupe, pour éviter que l'outil s'impose à des groupes qui n'en ont pas l'usage (formations informelles, groupes sandbox...).

## Utilisation côté stagiaire

Sur son tableau de bord `/stagiaire/outils`, la carte **« Émargement »** affiche un bouton **« Signer maintenant »** uniquement quand une séance est ouverte pour son groupe. Le stagiaire dessine sa signature du doigt/souris sur un pad, puis valide — la signature ne peut être posée qu'une seule fois par séance (une correction ultérieure ne peut venir que du formateur).

Une alerte apparaît aussi dans la **cloche de notification** (visible sur toutes les pages stagiaire, pas seulement Outils) dès qu'une séance s'ouvre pour l'un de ses groupes actifs et que sa signature est encore en attente — item « Émargement à signer » avec le nom du groupe, cliquable directement vers l'écran de signature. L'alerte disparaît d'elle-même une fois signée. Pas d'email pour l'instant (canal jugé moins fiable pour ce public, cf. décision ci-dessous) — seulement le canal in-app, cohérent avec Whiteboard/Mur de questions/Quiz live.

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

L'activation par groupe est un simple booléen `groups.emargement_enabled` (défaut `false`, même famille que `is_active`/`is_sandbox`) — ce n'est qu'un filtre d'affichage sur `EmargementController::index()` : désactiver un groupe ne touche ni ne supprime ses séances existantes, ça masque juste l'accès à la gestion depuis la vue dédiée tant qu'il n'est pas réactivé.

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

### Suite de tests

`tests/Feature/Emargement/` couvre la création de séance (roster, contrainte unique), l'ouverture/fermeture, la signature stagiaire (accès, refus si séance fermée ou déjà signée), la correction formateur (absent/signature déléguée), l'export PDF, et l'intégration dans Progression et les dashboards Outils.

---

[Retour au wiki](README.md)
