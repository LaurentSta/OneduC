# 07 — Outils d'animation pédagogique

*Public : formateurs pour la présentation des outils ; la partie technique en fin de page s'adresse aux développeurs.*

**Statut au 10 juillet 2026** : ces outils sont développés côté code (contrôleurs, routes et accès aux données) mais **ne sont pas encore activés en environnement de production**. Le contenu ci-dessous décrit le fonctionnement prévu/en place dans le code, pas une fonctionnalité disponible pour les formateurs aujourd'hui.

Oneduc intègre 13 activités live plus une intégration de pages collaboratives HedgeDoc. Ils fonctionnent en présentiel, en distanciel ou en hybride, et vivent dans le même environnement que les modules et les groupes. Une séance n'a donc pas besoin de trois onglets et deux comptes externes pour fonctionner.

Ces outils sont volontairement distincts de l'**émargement** (feuille de présence, document administratif à valeur d'audit) — voir la page dédiée [16 — Émargement](16-emargement.md).

Seuls les stagiaires membres du groupe peuvent participer à un outil actif. Les résultats s'affichent côté formateur avec un délai de 2 à 3 secondes.

---

## Les 14 outils

### 1. Quiz live

Le formateur lance un quiz en direct, les stagiaires répondent sur leur appareil, les résultats s'affichent au fil des réponses. Les questions peuvent venir de la banque de questions native. Les résultats sont distincts des quiz de formation : la progression n'est pas impactée. Fonctionne aussi bien sur grand écran en présentiel qu'en partage d'écran à distance.

### 2. Nuage de mots

Les stagiaires soumettent un ou plusieurs mots sur un thème donné. Le nuage se construit en temps réel, la taille des mots suit leur fréquence. Utile pour faire émerger les représentations initiales d'un groupe. Peut être intégré dans un parcours.

Le formateur peut l'utiliser comme outil autonome en choisissant un groupe et en lançant une session active, ou l'insérer dans un parcours. Côté stagiaire, l'accès reste centralisé : la carte "Nuage de mots" de `/stagiaire/outils` affiche un bouton de participation vers la session active ou, à défaut, vers le nuage du parcours actif. Quand un nuage autonome est ouvert pour son groupe, un message est créé dans `/stagiaire/messages`, une notification non lue alimente le macaron orange de la cloche, et l'alerte de cloche affiche aussi le code avec le lien de participation.

La vue stagiaire reste volontairement centrée sur la saisie des mots : elle n'affiche pas le nuage en direct. La projection du nuage collectif est portée par la vue live formateur, utilisable en présentiel comme en distanciel via partage d'écran.

Un formateur peut supprimer un nuage autonome depuis la liste des nuages. La suppression retire le nuage et ses réponses associées, sans supprimer le groupe ni les comptes stagiaires.

### 3. Sondage

Choix unique ou multiple. Le formateur crée les options, les stagiaires votent, les résultats s'affichent en barres ou en camembert. Se lance en quelques secondes. Peut être intégré dans un parcours.

### 4. Vrai ou Faux

Le formateur crée des affirmations courtes avec une réponse attendue (`Vrai` ou `Faux`) et, si besoin, une explication. Les stagiaires répondent depuis un code d'accès ; le formateur voit les résultats en direct et peut commenter les bonnes réponses. L'outil suit le pattern session/réponses avec `true_false_sessions` et `true_false_session_responses`.

L'outil est activable/désactivable séparément via `config('outils.vraifaux.enabled')` (`OUTILS_VRAIFAUX_ENABLED`). Quand il est désactivé, ses routes et sa tuile formateur ne sont pas exposées.

### 5. Buzzer Quiz

Le formateur prépare une série de questions, lance la manche, puis les stagiaires buzzent depuis leur appareil. Le plus rapide répond à voix haute ou à distance ; le formateur valide ou refuse la réponse et le classement se met à jour. L'outil s'appuie sur `buzzer_sessions`, `buzzer_questions`, `buzzer_attempts` et `buzzer_participants`.

L'outil est activable/désactivable via `config('outils.buzzer.enabled')` (`OUTILS_BUZZER_ENABLED`).

### 6. Échelle

Les stagiaires positionnent leur avis sur une échelle de 1 à N : niveau de confiance, accord/désaccord... Le formateur voit la distribution des réponses. Pratique pour évaluer le sentiment de compréhension en fin de séquence.

L'outil est activable/désactivable via `config('outils.echelle.enabled')` (`OUTILS_ECHELLE_ENABLED`).

### 7. Zone de clic

Le formateur importe une image, dessine des zones à retrouver et nomme chaque composant. Les stagiaires doivent cliquer au bon endroit ; leur score et la réussite par composant remontent côté formateur. L'outil utilise `component_finder_sessions` et `component_finder_attempts`.

L'outil est activable/désactivable via `config('outils.composants.enabled')` (`OUTILS_COMPOSANTS_ENABLED`).

### 8. Mur de questions

Les stagiaires posent leurs questions en texte libre. Le formateur les voit arriver en temps réel et peut les marquer comme traitées. Donne la parole à ceux qui n'osent pas la prendre, et évite de perdre les questions en distanciel.

### 9. Roue aléatoire

Le formateur charge une liste (participants, sujets, rôles), la roue tourne et désigne un élément au hasard. Inspiration : [Picker](https://github.com/koddsson/picker).

### 10. Tableau blanc collaboratif

Tableau blanc partagé basé sur [Excalidraw](https://github.com/excalidraw/excalidraw). Formateur et stagiaires dessinent et annotent ensemble. Un seul tableau blanc actif par groupe.

### 11. Minuteur

Minuteur visible par tous les participants, contrôlé par le formateur (démarrer, pause, réinitialiser). Un seul minuteur actif par groupe. Utile pour rythmer des ateliers ou des activités chronométrées.

### 12. Jeu du pendu

Le formateur choisit un mot ou une expression. Les stagiaires du groupe proposent collectivement des lettres et voient la partie évoluer toutes les trois secondes. Les co-formateurs du groupe peuvent aussi piloter la session. Les tables `hangman_sessions` et `hangman_guesses` sont gérées par le domaine autonome `app/Domains/Outils/Pendu/`.

Le domaine utilise Query Builder et des transactions SQL, sans modèle Eloquent. Il est activable avec `OUTILS_PENDU_ENABLED` ; désactivé, il n'enregistre ni routes, ni vues de dashboard, ni requêtes liées au Pendu.

### 13. Jeu de mémoire

Le formateur prépare de trois à dix paires. Le stagiaire retourne les cartes, puis son nombre de coups, ses erreurs et sa durée alimentent le classement en direct. Le serveur recalcule les erreurs à partir du nombre de paires et de coups au lieu de faire confiance à la valeur envoyée par le navigateur.

Les tables `memory_sessions` et `memory_attempts`, les routes et les vues sont contenues dans `app/Domains/Outils/Memoire/`. Le domaine fonctionne sans modèle Eloquent et s'active avec `OUTILS_MEMOIRE_ENABLED`.

### 14. Pages collaboratives (HedgeDoc)

Accès formateur à une page collaborative externe HedgeDoc, via `/formateur/pages-collaboratives`. Contrairement aux autres outils, il n'y a ni participation stagiaire ni stockage de réponses dans Oneduc — c'est une redirection vers l'instance HedgeDoc configurée.

---

## Côté stagiaire

Les outils actifs de son groupe sont regroupés dans un tableau de bord unique ("Activités de groupe"). Limite actuelle : comme pour les modules, un stagiaire dans plusieurs groupes actifs ne voit que le premier.

## Accès contextuel depuis une formation

Depuis les vues formateur d'un chapitre ou d'une leçon, l'action "Lancer une activité" ouvre un panneau latéral léger plutôt qu'un cockpit permanent. Le groupe, le module et la leçon courants sont préremplis lorsque l'URL contient déjà le contexte (`mode=groupe`, `group_id`, module, chapitre, leçon).

Le panneau permet notamment de lancer un quiz en direct, d'ouvrir le tableau blanc du groupe ou d'accéder à la personnalisation de la formation. Il reste fermé par défaut afin que la consultation des leçons ne soit pas concurrencée par les outils d'animation.

---

## Idées d'outils futurs

Huit pistes produit supplémentaires sont identifiées. Détail dans [docs/idees-outils-formateurs.md](../idees-outils-formateurs.md) :

| ID | Outil | Priorité |
|----|-------|----------|
| OF-001 | Cockpit de séance formateur (tableau de pilotage unifié) | Haute |
| OF-002 | Ticket de sortie (micro-bilan fin de séquence) | Haute |
| OF-003 | Mur de questions anonyme avec vote | Haute |
| OF-004 | Émargement par QR code (l'émargement par signature existe déjà, voir [page 16](16-emargement.md) — le scan QR reste à faire) | Moyenne |
| OF-005 | Générateur d'activités par IA | Moyenne |
| OF-006 | Groupes intelligents (binômes, sous-groupes, rôles) | Moyenne |
| OF-007 | Débrief / rétrospective de session | Moyenne |
| OF-008 | Analytics pédagogiques avancées | Moyenne |

---

## Partie technique

### Pattern commun des activités live

Les activités avec participation stagiaire suivent généralement la même architecture :

```sql
-- Table de session
{outil}_sessions (
  id, group_id, formateur_id,
  is_active BOOL,     -- session ouverte ou fermée
  access_code VARCHAR, -- code court pour rejoindre
  created_at, updated_at
)

-- Table de réponses
{outil}_responses (
  id, user_id, session_id,
  [données spécifiques à l'outil],
  created_at
)
```

| Espace | Rôle |
|--------|------|
| `Formateur/{Outil}Controller` | CRUD sessions + lancer/fermer + endpoint JSON résultats |
| `Stagiaire/{Outil}Controller` | Afficher le formulaire de participation + soumettre une réponse |

**Temps réel** : polling AJAX toutes les 2–3 secondes via Alpine.js `setInterval`. Pas de WebSockets — ce choix simplifie le déploiement mais limite la réactivité à quelques secondes.

**Vérification d'accès stagiaire** :

```php
$group->students()->where('users.id', auth()->id())->exists()
```

Exceptions au pattern :
- le Pendu et le Jeu de mémoire sont entièrement encapsulés dans leur domaine, avec vues namespacées, migrations locales, routes et providers dédiés ; leurs dépôts utilisent Query Builder sans Eloquent ;
- le Buzzer Quiz ajoute une table de questions, une table de tentatives et une table de participants pour gérer le classement ;
- Zone de clic stocke l'image et les zones à trouver dans la session, puis les scores dans `component_finder_attempts` ;
- le tableau blanc a son propre modèle de snapshot/éléments Excalidraw (`GroupWhiteboard`, relation unique par groupe) ;
- le minuteur n'a pas de table de réponses (`GroupTimer`, relation unique par groupe) ;
- les pages collaboratives redirigent vers HedgeDoc (configuration `HEDGEDOC_BASE_URL` et `HEDGEDOC_NEW_PATH` ; si l'URL n'est pas configurée, l'interface affiche les variables à ajouter dans `.env`).

Le minuteur sert de pilote pour l'architecture "outil auto-contenu". Le Pendu et le Jeu de mémoire vont plus loin : chaque provider charge sa configuration, ses routes, ses migrations, ses vues namespacées et ses ajouts aux dashboards. Les seuls raccords génériques sont l'enregistrement des providers et les points d'extension Blade des hubs ; aucun contrôleur central ne connaît ces deux outils. Les variables `OUTILS_PENDU_ENABLED` et `OUTILS_MEMOIRE_ENABLED` permettent de les retirer complètement du runtime indépendamment l'un de l'autre.

### Exploitation du Pendu et du Jeu de mémoire

| Outil | Clé de configuration | Variable d'environnement | Tables | Espace formateur | Participation |
|-------|----------------------|--------------------------|--------|-------------------|---------------|
| Pendu | `outils.pendu.enabled` | `OUTILS_PENDU_ENABLED` | `hangman_sessions`, `hangman_guesses` | `/formateur/pendu` | `/oneduc/pendu` |
| Jeu de mémoire | `outils.memoire.enabled` | `OUTILS_MEMOIRE_ENABLED` | `memory_sessions`, `memory_attempts` | `/formateur/memoire` | `/oneduc/memoire` |

Les deux variables valent `true` par défaut. Pour préparer un environnement et activer les domaines :

```dotenv
OUTILS_PENDU_ENABLED=true
OUTILS_MEMOIRE_ENABLED=true
```

```bash
php artisan config:clear
php artisan migrate --force
php artisan config:cache
```

La migration doit être exécutée avant d'ouvrir les outils. Les providers vérifient l'existence de leurs tables avant d'ajouter leurs tuiles aux dashboards, mais leurs routes de gestion nécessitent bien le schéma correspondant. Cette étape évite les erreurs SQL de type « table inexistante » lors du premier accès.

Pour désactiver un seul domaine, positionner sa variable à `false`, puis reconstruire le cache de configuration :

```dotenv
OUTILS_PENDU_ENABLED=false
OUTILS_MEMOIRE_ENABLED=true
```

```bash
php artisan config:cache
```

La désactivation retire les routes et les tuiles du domaine concerné sans toucher à ses tables ni aux autres outils. Les données existantes sont conservées pour une réactivation ultérieure. L'activation est actuellement une opération de configuration et de déploiement : aucun interrupteur n'est encore disponible dans l'interface d'administration.

Règles d'accès communes :

- le formateur responsable du groupe et ses co-formateurs peuvent créer, piloter et supprimer les sessions ;
- seuls les stagiaires rattachés au groupe peuvent envoyer une lettre ou enregistrer une tentative ;
- les formateurs autorisés peuvent prévisualiser la participation, mais ne peuvent pas soumettre de réponse stagiaire ;
- un utilisateur extérieur au groupe reçoit une réponse `403` ;
- les soumissions sont limitées par un throttle pour réduire les envois abusifs.

Le Pendu verrouille la session pendant l'enregistrement d'une lettre afin d'éviter deux mises à jour concurrentes. Le Jeu de mémoire n'accepte qu'une tentative enregistrée par stagiaire et recalcule les erreurs côté serveur à partir du nombre de paires et de coups.

### Page d'entrée formateur (grille d'outils)

`OutilsNumeriquesController::index()` (route `/formateur/outils-numeriques`) sert de hub : il agrège les sessions récentes de chaque outil et la liste des groupes du formateur, puis les passe à la vue `formateur.outils.index`.

Chaque outil est une tuile compacte (icône + titre) dans une grille à gauche ; un panneau fixe à droite (`#outil-detail-panel`) affiche un texte générique par défaut et bascule sur la description, les badges (Présentiel/Distanciel/...) et les sessions récentes de l'outil cliqué. Ce comportement est porté par le composant réutilisable `x-oneduc.outil-tile` (`resources/views/components/oneduc/outil-tile.blade.php`), avec les slots `icon`, `description`, `badges` et `body` : le contenu de détail est déplacé vers le panneau de droite via `x-teleport` (Alpine.js) et affiché avec `x-show="selectedTool === '...'"`, ce qui évite tout décalage de la grille (l'ancienne info-bulle au survol décalait la mise en page). L'état `selectedTool` et la barre de filtres "Famille" (`filtre`) sont portés par le conteneur parent (`formateur.outils.index`).

Pour un outil historique, invoquer `<x-oneduc.outil-tile>` avec ses props (`title`, `icon-bg`, `tool-id` unique, `cta-route`, `cta-label`, `cta-bg`, `badge-count` optionnel) et ses slots. Un outil autonome enregistre plutôt sa vue de tuile depuis son provider dans le point d'extension `$outilsAutonomes`, sans modifier `OutilsNumeriquesController`.

### Agrégateur stagiaire

`StagiaireController::StagiaireOutils()` récupère les sessions liées au premier groupe actif du stagiaire — d'où la limite multi-groupe mentionnée plus haut.

Pour le nuage de mots, l'agrégateur additionne les sessions autonomes `WordCloud` du groupe et les items `wordcloud` du parcours actif. Le lien d'action privilégie une session autonome active (`wordcloud.join.code`) puis bascule vers le premier item de parcours (`stagiaire.wordcloud.parcours.show`). La cloche utilise `stagiaire.wordcloud.notification-status` pour signaler uniquement les sessions autonomes ouvertes. Le nombre affiché sur le macaron orange de la cloche correspond en priorité aux notifications non lues du stagiaire.

---

[Retour au wiki](README.md)
