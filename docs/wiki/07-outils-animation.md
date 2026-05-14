# 07 — Outils d'animation pédagogique

Oneduc intègre nativement **9 outils d'animation** utilisables en présentiel, distanciel ou hybride, sans plugin externe. C'est l'une des forces différenciantes majeures de la plateforme par rapport aux LMS généralistes.

---

## Pattern commun à tous les outils

Chaque outil suit la même architecture :

### Structure de données

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

### Contrôleurs

| Espace | Rôle |
|--------|------|
| `Formateur/{Outil}Controller` | CRUD sessions + lancer/fermer + endpoint JSON résultats |
| `Stagiaire/{Outil}Controller` | Afficher le formulaire de participation + soumettre une réponse |

### Temps réel

Les résultats en temps réel sont obtenus par **polling AJAX** toutes les 2–3 secondes via Alpine.js `setInterval`. Il n'y a pas de WebSockets — ce choix simplifie le déploiement mais limite la réactivité à quelques secondes.

### Vérification d'accès stagiaire

```php
$group->students()->where('users.id', auth()->id())->exists()
```

Seuls les stagiaires membres du groupe peuvent accéder à un outil actif.

---

## Les 9 outils

### 1. Quiz live

Permet de lancer un quiz en temps réel pendant une session. Les stagiaires répondent sur leur appareil, le formateur voit les résultats s'afficher progressivement.

- Le contenu des questions peut provenir de la banque de questions native
- Les résultats sont distincts des tentatives de quiz "formation" (la progression SCORM n'est pas impactée)
- Adapté au présentiel (grand écran) et distanciel (partage d'écran + code d'accès)

### 2. Nuage de mots (Word Cloud)

Les stagiaires soumettent un ou plusieurs mots sur un thème donné par le formateur. Le nuage se construit en temps réel, avec une taille des mots proportionnelle à leur fréquence.

- Utile pour explorer les représentations initiales
- Peut être inclus dans un `FormateurParcoursItem`
- Accessible dans l'agrégateur d'outils stagiaire (`StagiaireController::StagiaireOutils()`)

### 3. Sondage (Poll)

Sondage à choix multiples ou unique. Le formateur crée les options, les stagiaires votent, les résultats s'affichent en barres ou camembert.

- Peut être intégré dans un parcours `FormateurParcours`
- Rapide à lancer (quelques secondes)

### 4. Échelle (Scale)

Les stagiaires positionnent leur avis sur une échelle de 1 à N (ex. : niveau de confiance, accord/désaccord). La distribution des réponses est affichée au formateur.

- Utile pour évaluer le sentiment de compréhension en fin de séquence
- Complémentaire du ticket de sortie (fonctionnalité future — voir [idées d'outils](../idees-outils-formateurs.md))

### 5. Mur de questions (Question Wall)

Les stagiaires posent des questions en texte libre. Le formateur voit toutes les questions en temps réel et peut les marquer comme traitées.

- Favorise la participation des apprenants qui n'osent pas parler
- Particulièrement utile en distanciel pour ne pas perdre les questions

### 6. Roue aléatoire (Random Wheel)

Le formateur charge une liste de participants (ou d'items). La roue tourne et désigne un élément au hasard.

- Sélection d'un stagiaire pour répondre à une question
- Désignation aléatoire d'un sujet ou d'un rôle dans un atelier
- Inspiration : [Picker (GitHub)](https://github.com/koddsson/picker)

### 7. Tableau blanc collaboratif (Whiteboard)

Tableau blanc partagé basé sur **Excalidraw** (open source). Le formateur et les stagiaires peuvent dessiner, annoter et collaborer en temps réel.

- Intégration complète d'Excalidraw dans l'interface
- Un seul tableau blanc actif par groupe (`GroupWhiteboard` — relation unique)
- Inspiration : [Excalidraw (GitHub)](https://github.com/excalidraw/excalidraw)

### 8. Minuteur (Timer)

Minuteur de session visible pour tous les participants. Le formateur le contrôle (démarrer, pause, réinitialiser).

- Un seul minuteur actif par groupe (`GroupTimer` — relation unique)
- Utile pour structurer des activités chronométrées (ateliers, quiz papier, brainstorming)

---

## Tableau de bord "Outils" stagiaire

Les outils actifs sont agrégés pour le stagiaire dans `StagiaireController::StagiaireOutils()`. Cette méthode récupère toutes les sessions actives des groupes du stagiaire et les présente dans un tableau de bord unifié.

---

## Idées d'outils futurs

Huit pistes produit supplémentaires ont été identifiées. Voir [docs/idees-outils-formateurs.md](../idees-outils-formateurs.md) pour le détail :

| ID | Outil | Priorité |
|----|-------|----------|
| OF-001 | Cockpit de séance formateur (tableau de pilotage unifié) | Haute |
| OF-002 | Ticket de sortie (micro-bilan fin de séquence) | Haute |
| OF-003 | Mur de questions anonyme avec vote | Haute |
| OF-004 | Émargement par QR code | Moyenne |
| OF-005 | Générateur d'activités par IA | Moyenne |
| OF-006 | Groupes intelligents (binômes, sous-groupes, rôles) | Moyenne |
| OF-007 | Débrief / rétrospective de session | Moyenne |
| OF-008 | Analytics pédagogiques avancées | Moyenne |

---

[Retour au wiki](README.md)
