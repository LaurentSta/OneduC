# 07 — Outils d'animation pédagogique

*Public : formateurs pour la présentation des outils ; la partie technique en fin de page s'adresse aux développeurs.*

**Statut au 14 juillet 2026** : ces outils sont développés côté code (contrôleurs, routes et accès aux données) mais **ne sont pas encore activés en environnement de production**. Le contenu ci-dessous décrit le fonctionnement prévu/en place dans le code, pas une fonctionnalité disponible pour les formateurs aujourd'hui.

Oneduc intègre 13 activités live plus une intégration de pages collaboratives HedgeDoc. Ils fonctionnent en présentiel, en distanciel ou en hybride, et vivent dans le même environnement que les modules et les groupes. Une séance n'a donc pas besoin de trois onglets et deux comptes externes pour fonctionner.

Ces outils sont volontairement distincts de l'**émargement** (feuille de présence, document administratif à valeur d'audit) — voir la page dédiée [16 — Émargement](16-emargement.md).

En règle générale, seuls les stagiaires membres du groupe peuvent participer à un outil actif, et les résultats s'affichent côté formateur avec un délai de 2 à 3 secondes. Trois outils dérogent volontairement à la vérification d'appartenance au groupe côté participation, car ils sont pensés pour une projection ou une lecture ouverte plutôt qu'une identification individuelle : le [nuage de mots](outils/nuage-de-mots.md) autonome (aucune authentification requise pour soumettre un mot), la [roue aléatoire](outils/roue-aleatoire.md) (vue de tirage en lecture seule) et la lecture du [mur de questions](outils/mur-de-questions.md) (poser une question ou voter reste protégé). Le détail est précisé sur la fiche de chaque outil.

---

## Les 14 outils

Chaque outil a désormais sa propre fiche dédiée : fonctionnement, contexte d'usage pédagogique, modalités (synchrone/asynchrone, présentiel/distanciel, individuel/collectif) et partie technique (routes, tables, configuration). Cette page reste le point d'entrée et regroupe ce qui est commun à tous.

| # | Outil | Résumé | Synchrone/Asynchrone | Présentiel/Distanciel | Participation |
|---|-------|--------|-----------------------|------------------------|----------------|
| 1 | [Quiz live](outils/quiz-live.md) | Quiz en direct sur la banque de questions d'une leçon | Synchrone | Les deux | Collective |
| 2 | [Nuage de mots](outils/nuage-de-mots.md) | Mots soumis en direct, taille selon fréquence | Asynchrone (saisie libre) | Les deux | Individuelle → rendu collectif |
| 3 | [Sondage](outils/sondage.md) | Choix unique/multiple, résultats en barres/camembert | Synchrone | Les deux | Individuelle → rendu collectif |
| 4 | [Vrai ou Faux](outils/vrai-ou-faux.md) | Affirmations courtes avec explication | Synchrone | Les deux | Individuelle → débrief collectif |
| 5 | [Buzzer Quiz](outils/buzzer-quiz.md) | Le plus rapide répond, classement en direct | Synchrone | Les deux | Individuelle, compétitive |
| 6 | [Échelle](outils/echelle.md) | Curseur de 1 à 10, moyenne et distribution | Synchrone | Les deux | Individuelle → rendu collectif |
| 7 | [Zone de clic](outils/zone-de-clic.md) | Cliquer sur les bons composants d'une image | Synchrone | Les deux | Individuelle, notée |
| 8 | [Mur de questions](outils/mur-de-questions.md) | Questions libres, anonymat optionnel, vote | Asynchrone | Les deux | Individuelle + votes |
| 9 | [Roue aléatoire](outils/roue-aleatoire.md) | Tirage au sort projeté à l'écran | Synchrone | Les deux | Spectateur (pas de compte requis) |
| 10 | [Tableau blanc](outils/tableau-blanc.md) | Espace Excalidraw partagé, un par groupe | Synchrone | Les deux | Collaborative |
| 11 | [Minuteur](outils/minuteur.md) | Compte à rebours partagé, un par groupe | Synchrone | Les deux | Spectateur |
| 12 | [Jeu du pendu](outils/pendu.md) | Un mot deviné collectivement, lettre par lettre | Synchrone | Les deux | Collective |
| 13 | [Jeu de mémoire](outils/memoire.md) | Paires à retrouver, classement individuel | Asynchrone | Les deux | Individuelle |
| 14 | [Pages collaboratives (HedgeDoc)](outils/pages-collaboratives.md) | Document Markdown coédité, externe à Oneduc | Les deux | Les deux | Collaborative, non tracée |

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
