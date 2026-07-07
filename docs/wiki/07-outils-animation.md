# 07 — Outils d'animation pédagogique

*Public : formateurs pour la présentation des outils ; la partie technique en fin de page s'adresse aux développeurs.*

**Statut au 5 juillet 2026** : ces outils sont développés côté code (modèles, contrôleurs, routes) mais **ne sont pas encore activés en environnement de production**. Le contenu ci-dessous décrit le fonctionnement prévu/en place dans le code, pas une fonctionnalité disponible pour les formateurs aujourd'hui.

Oneduc intègre 8 activités live plus une intégration de pages collaboratives HedgeDoc. Ils fonctionnent en présentiel, en distanciel ou en hybride, et vivent dans le même environnement que les modules et les groupes. Une séance n'a donc pas besoin de trois onglets et deux comptes externes pour fonctionner.

Ces outils sont volontairement distincts de l'**émargement** (feuille de présence, document administratif à valeur d'audit) — voir la page dédiée [16 — Émargement](16-emargement.md).

Seuls les stagiaires membres du groupe peuvent participer à un outil actif. Les résultats s'affichent côté formateur avec un délai de 2 à 3 secondes.

---

## Les 9 outils

### 1. Quiz live

Le formateur lance un quiz en direct, les stagiaires répondent sur leur appareil, les résultats s'affichent au fil des réponses. Les questions peuvent venir de la banque de questions native. Les résultats sont distincts des quiz de formation : la progression n'est pas impactée. Fonctionne aussi bien sur grand écran en présentiel qu'en partage d'écran à distance.

### 2. Nuage de mots

Les stagiaires soumettent un ou plusieurs mots sur un thème donné. Le nuage se construit en temps réel, la taille des mots suit leur fréquence. Utile pour faire émerger les représentations initiales d'un groupe. Peut être intégré dans un parcours.

### 3. Sondage

Choix unique ou multiple. Le formateur crée les options, les stagiaires votent, les résultats s'affichent en barres ou en camembert. Se lance en quelques secondes. Peut être intégré dans un parcours.

### 4. Échelle

Les stagiaires positionnent leur avis sur une échelle de 1 à N : niveau de confiance, accord/désaccord... Le formateur voit la distribution des réponses. Pratique pour évaluer le sentiment de compréhension en fin de séquence.

### 5. Mur de questions

Les stagiaires posent leurs questions en texte libre. Le formateur les voit arriver en temps réel et peut les marquer comme traitées. Donne la parole à ceux qui n'osent pas la prendre, et évite de perdre les questions en distanciel.

### 6. Roue aléatoire

Le formateur charge une liste (participants, sujets, rôles), la roue tourne et désigne un élément au hasard. Inspiration : [Picker](https://github.com/koddsson/picker).

### 7. Tableau blanc collaboratif

Tableau blanc partagé basé sur [Excalidraw](https://github.com/excalidraw/excalidraw). Formateur et stagiaires dessinent et annotent ensemble. Un seul tableau blanc actif par groupe.

### 8. Minuteur

Minuteur visible par tous les participants, contrôlé par le formateur (démarrer, pause, réinitialiser). Un seul minuteur actif par groupe. Utile pour rythmer des ateliers ou des activités chronométrées.

### 9. Pages collaboratives (HedgeDoc)

Accès formateur à une page collaborative externe HedgeDoc, via `/formateur/pages-collaboratives`. Contrairement aux autres outils, il n'y a ni participation stagiaire ni stockage de réponses dans Oneduc — c'est une redirection vers l'instance HedgeDoc configurée.

---

## Côté stagiaire

Les outils actifs de son groupe sont regroupés dans un tableau de bord unique ("Activités de groupe"). Limite actuelle : comme pour les modules, un stagiaire dans plusieurs groupes actifs ne voit que le premier.

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
- le tableau blanc a son propre modèle de snapshot/éléments Excalidraw (`GroupWhiteboard`, relation unique par groupe) ;
- le minuteur n'a pas de table de réponses (`GroupTimer`, relation unique par groupe) ;
- les pages collaboratives redirigent vers HedgeDoc (configuration `HEDGEDOC_BASE_URL` et `HEDGEDOC_NEW_PATH` ; si l'URL n'est pas configurée, l'interface affiche les variables à ajouter dans `.env`).

Le minuteur sert de pilote pour l'architecture "outil auto-contenu" : contrôleurs et garde d'accès dans `app/Domains/Outils/Minuteur/`, routes enregistrées par `MinuteurServiceProvider`, activable via `config('outils.minuteur.enabled')` (`OUTILS_MINUTEUR_ENABLED`). Ce pattern est évalué comme modèle avant généralisation aux autres outils.

### Page d'entrée formateur (grille d'outils)

`OutilsNumeriquesController::index()` (route `/formateur/outils-numeriques`) sert de hub : il agrège les sessions récentes de chaque outil et la liste des groupes du formateur, puis les passe à la vue `formateur.outils.index`.

Chaque outil est une tuile compacte (icône + titre) dans une grille à gauche ; un panneau fixe à droite (`#outil-detail-panel`) affiche un texte générique par défaut et bascule sur la description, les badges (Présentiel/Distanciel/...) et les sessions récentes de l'outil cliqué. Ce comportement est porté par le composant réutilisable `x-oneduc.outil-tile` (`resources/views/components/oneduc/outil-tile.blade.php`), avec les slots `icon`, `description`, `badges` et `body` : le contenu de détail est déplacé vers le panneau de droite via `x-teleport` (Alpine.js) et affiché avec `x-show="selectedTool === '...'"`, ce qui évite tout décalage de la grille (l'ancienne info-bulle au survol décalait la mise en page). L'état `selectedTool` et la barre de filtres "Famille" (`filtre`) sont portés par le conteneur parent (`formateur.outils.index`).

Pour ajouter un outil à cette page : invoquer `<x-oneduc.outil-tile>` avec ses props (`title`, `icon-bg`, `tool-id` unique, `cta-route`, `cta-label`, `cta-bg`, `badge-count` optionnel) et ses slots. Pas besoin de dupliquer la structure de carte.

### Agrégateur stagiaire

`StagiaireController::StagiaireOutils()` récupère les sessions liées au premier groupe actif du stagiaire — d'où la limite multi-groupe mentionnée plus haut.

---

[Retour au wiki](README.md)
