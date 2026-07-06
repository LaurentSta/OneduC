# 15 — Génération de contenu par IA (Mistral)

*Public : formateurs (usage) et développeurs (partie technique en fin de page).*

## Vue d'ensemble

Le builder formateur intègre une génération de contenu assistée par IA, via l'API **Mistral** (choisie notamment pour son hébergement européen, un critère RGPD plus simple à défendre que des fournisseurs hors UE). Deux usages distincts :

1. **Générer une leçon** à partir d'un document existant, à l'intérieur d'un chapitre déjà créé.
2. **Générer une formation complète** (chapitres + leçons + contenu rédigé) à partir d'un thème et/ou d'un document, dès l'écran de création.

Dans les deux cas, **rien n'est publié automatiquement** : le contenu généré atterrit dans l'éditeur normal du builder, à relire et ajuster par le formateur avant de le proposer aux stagiaires.

## Utilisation côté formateur

### Générer une leçon depuis un document

Depuis le plan d'une formation (`/formateur/mes-modules/{id}/edition`), bouton **« + Générer une leçon (IA) »** à côté de « + Ajouter une leçon ». Une modale permet de :
- choisir le chapitre de destination ;
- donner un titre (optionnel — l'IA en suggère un sinon) ;
- importer un document source : **PDF, Word (.docx) ou texte brut, 20 Mo max**.

Seul du contenu texte structuré est généré (titres, paragraphes, listes) — pas d'image, vidéo ou SCORM, qui nécessitent des médias déjà importés dans le module.

### Générer une formation complète depuis un thème

Depuis l'écran **« Créer une formation »**, onglet **« Générer avec l'IA »** (à côté de « Créer manuellement »). Deux champs, dont au moins un est requis :
- un **thème** en texte libre (500 caractères max) ;
- et/ou un **document source** (PDF, .docx ou texte brut, 20 Mo max).

L'IA propose 3 à 5 chapitres de 2 à 4 leçons chacune, avec un contenu déjà rédigé pour chaque leçon. La génération peut prendre **jusqu'à 4 minutes** — la page affiche un indicateur de chargement pendant l'attente.

### Limites à connaître

- **Quota** : 3 générations IA par jour et par formateur (les deux usages ci-dessus partagent le même compteur). Au-delà, un message invite à réessayer le lendemain.
- **Modération de contenu** : un thème ou document manifestement inapproprié (violence, haine, contenu sexuel, etc.) est refusé immédiatement, sans consommer le quota.
- Le contenu généré doit être relu : l'IA peut se tromper ou mal interpréter un document mal structuré.

---

## Partie technique

### Configuration

| Variable | Rôle | Requis |
|----------|------|--------|
| `MISTRAL_API_KEY` | Clé API Mistral | Oui (fonctionnalité désactivée sans clé) |
| `MISTRAL_MODEL` | Modèle de chat utilisé | Non (défaut : `mistral-large-latest`) |

Lues via `config('services.mistral.*')` (`config/services.php`). La modération utilise toujours le modèle `mistral-moderation-latest`, non configurable.

### Composants (`app/Domains/ModulesFormateur/`)

| Fichier | Rôle |
|---------|------|
| `Support/MistralClient.php` | Appels HTTP bruts à l'API Mistral : `chat()` (complétion JSON) et `moderate()` (modération) |
| `Support/ExtracteurTexteDocument.php` | Extraction de texte : `smalot/pdfparser` (PDF), `phpoffice/phpword` (.docx), lecture directe (.txt) |
| `Support/GardeFouPromptIA.php` | Vérifie le thème/document via `MistralClient::moderate()`, lève une exception si contenu bloquant |
| `Support/LimiteurGenerationIA.php` | Quota de 3 générations/jour/formateur via `RateLimiter` (cache Laravel, table `cache`) |
| `Actions/GenererLeconIA.php` | Orchestration : document → texte → garde-fous → prompt → blocs de leçon |
| `Actions/GenererStructureFormationIA.php` | Orchestration : thème/document → texte → garde-fous → prompt → module + chapitres + leçons |

### Flux — génération de leçon

1. `ModuleBuilderController::generateLectureIA()` (route `formateur.modules.builder.lectures.generate-ia`, POST `sections/{section}/lectures/generer-ia`) valide le fichier et délègue à `GenererLeconIA::execute()`.
2. `ExtracteurTexteDocument` extrait le texte brut du document.
3. `MistralClient::chat()` appelle l'API Mistral en mode JSON strict, avec un prompt qui structure le document en blocs `text` pédagogiques et suggère un titre.
4. La réponse passe par le `NettoyeurBlocsModule` existant (même sanitizer que l'éditeur manuel) avant d'être persistée via `CreerLecon`, dans le chapitre choisi.

### Flux — génération de formation complète

1. `ModuleBuilderController::generateStructureIA()` (route `formateur.modules.builder.generate-structure-ia`, POST `/mes-modules/generer-ia`) valide thème/document et appelle `GenererStructureFormationIA::execute()`. `set_time_limit(270)` est posé avant l'appel : la génération complète peut prendre 1 à 4 minutes.
2. Si un document est fourni, `ExtracteurTexteDocument` en extrait le texte. Le thème et/ou le texte du document sont combinés dans le prompt utilisateur envoyé à Mistral.
3. `MistralClient::chat()` est appelé avec un timeout de 240 s et jusqu'à 8000 tokens de réponse, en mode JSON strict. Le prompt demande 3 à 5 chapitres de 2 à 4 leçons, chaque leçon avec un contenu déjà rédigé (blocs `text`).
4. Le module est créé via `CreerModule::creerModuleVide()` (variante de `CreerModule::execute()` sans la structure d'exemple), puis chapitres et leçons sont créés dans une transaction (`CreerChapitre`, `CreerLecon` — même sanitizer que le reste du builder).
5. Redirection vers le plan du module généré.

Point d'attention : `Illuminate\Http\Client\ConnectionException` (timeout réseau vers Mistral) n'hérite pas de `RuntimeException` — `generateStructureIA()` et `generateLectureIA()` catchent donc `ConnectionException` séparément (message générique « a pris trop de temps ») de `RuntimeException` (message spécifique : quota, modération, réponse invalide), sinon un timeout Mistral remonte en erreur 500 au lieu d'un message utilisateur.

### Garde-fous : modération et quota

Les deux actions IA appliquent systématiquement, dans cet ordre, avant tout appel de génération coûteux :

1. **Quota** (`LimiteurGenerationIA`) : vérifie via `RateLimiter::tooManyAttempts()` qu'un formateur n'a pas dépassé **3 générations IA par jour** (clé `ia-generation-formateur:{id}`, fenêtre glissante de 24 h). Si dépassé, lève une `RuntimeException` immédiatement, avant tout appel réseau.
2. **Modération** (`GardeFouPromptIA` + `MistralClient::moderate()`) : envoie le thème et/ou le texte extrait du document à l'endpoint `POST /v1/moderations` (modèle `mistral-moderation-latest`). Catégories bloquantes : `sexual`, `hate_and_discrimination`, `violence_and_threats`, `dangerous`, `criminal`, `selfharm`, `jailbreaking`. **Volontairement exclues** : `health`, `financial`, `law`, `pii` — une plateforme de formation professionnelle traite légitimement ces sujets (premiers secours, gestion budgétaire, droit du travail, protection des données), les inclure aurait généré trop de faux positifs. Rejet immédiat (`RuntimeException` avec le nom des catégories déclenchées) si une catégorie bloquante est détectée à `true`.
3. Seulement si la modération passe, `LimiteurGenerationIA::enregistrerTentative()` incrémente le compteur de quota — **juste avant** l'appel de génération proprement dit, donc un contenu rejeté par la modération ne consomme pas de quota, mais un timeout Mistral (après modération) en consomme un (l'appel a réellement eu lieu).

`GenererLeconIA::execute()` et `GenererStructureFormationIA::execute()` prennent donc un paramètre `int $trainerId` en plus du document/thème.

### Dépendances Composer

| Package | Usage |
|---------|-------|
| `smalot/pdfparser` | Extraction de texte PDF |
| `phpoffice/phpword` | Extraction de texte Word `.docx` |
