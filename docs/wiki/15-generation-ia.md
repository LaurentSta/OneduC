# 15 — Génération de contenu par IA

*Public : formateurs et administrateurs de contenu (usage), développeurs (partie technique en fin de page).*

## Vue d'ensemble

Le builder formateur et le nouveau constructeur admin intègrent plusieurs générations de contenu assistées par IA :

1. **Générer une leçon** à partir d'un document existant, à l'intérieur d'un chapitre déjà créé — via l'API **Mistral** (choisie notamment pour son hébergement européen, un critère RGPD plus simple à défendre que des fournisseurs hors UE).
2. **Générer une formation complète** (chapitres + leçons + contenu rédigé) à partir d'un thème et/ou d'un document, dès l'écran de création — même moteur Mistral.
3. **Générer l'audio d'une leçon** (lecture du texte à voix haute) — via **Piper**, un moteur de synthèse vocale auto-hébergé (gratuit, open-source), plutôt qu'une API cloud payante.

Dans tous les cas, **rien n'est publié automatiquement** : le contenu généré atterrit dans l'éditeur normal, à relire et ajuster. Dans le catalogue admin, il reste au statut brouillon jusqu'à une action de publication explicite.

## Utilisation côté formateur

### Générer une leçon depuis un document

Depuis le plan d'une formation (`/formateur/mes-modules/{id}/edition`), bouton **« + Générer une leçon (IA) »** à côté de « + Ajouter une leçon ». Une modale permet de :
- choisir le chapitre de destination ;
- donner un titre (optionnel — l'IA en suggère un sinon) ;
- importer un document source : **PDF, Word (.docx) ou texte brut, 20 Mo max**.

Le contenu généré est essentiellement du texte structuré (titres, paragraphes, listes). Si le document source contient des images, elles sont automatiquement extraites et ajoutées **à la fin de la leçon** (voir « Images extraites des documents » ci-dessous) — pas de vidéo ou SCORM, qui nécessitent des médias déjà importés dans le module.

### Générer une formation complète depuis un thème

Depuis l'écran **« Créer une formation »**, onglet **« Générer avec l'IA »** (à côté de « Créer manuellement »). Deux champs, dont au moins un est requis :
- un **thème** en texte libre (500 caractères max) ;
- et/ou un **document source** (PDF, .docx ou texte brut, 20 Mo max).

L'IA propose 3 à 5 chapitres de 2 à 4 leçons chacune, avec un contenu déjà rédigé pour chaque leçon, ainsi que 3 à 5 **objectifs pédagogiques** pour la formation. La génération peut prendre **jusqu'à 4 minutes** — la page affiche un indicateur de chargement pendant l'attente.

### Utilisation côté administrateur

Le constructeur `/admin/formations/constructeur` expose les mêmes générations de structure, de leçon, de quiz et d'audio dans le contexte du catalogue officiel. La structure générée devient un brouillon appartenant au catalogue Oneduc ; l'administrateur peut lui associer un formateur référent facultatif, compléter ses métadonnées, ses médias, ses quiz et ses blocs SCORM, conserver les leçons SCORM/slides natives issues d'une copie, puis prévisualiser l'ensemble avant publication.

L'activité IA de l'administration utilise un quota **plateforme** séparé de ceux des formateurs. Plusieurs comptes admin partagent donc le même compteur quotidien admin et le même budget mensuel admin ; leurs appels ne consomment pas le quota personnel d'un formateur, même lorsqu'un formateur référent est associé à la formation.

### Objectifs pédagogiques

Les objectifs générés par l'IA sont enregistrés sur la formation et visibles dans l'onglet **« Objectifs »** de sa page de présentation (le même endroit où s'affichent les objectifs saisis manuellement). Ils sont modifiables à tout moment dans le panneau **« Options de la formation »** du plan de la formation (champ « Objectifs pédagogiques », un par ligne) — comme le reste du contenu généré par IA, à relire avant de proposer la formation aux stagiaires.

### Images extraites des documents

Quand un document source (PDF ou Word) contient des images, elles sont automatiquement récupérées et rattachées à la formation, sans intervention du formateur :
- **Word (.docx)** : toutes les images du document (png, jpg, gif, webp), extraites intégralement.
- **PDF** : seules les images encodées en **JPEG** sont récupérées (format le plus courant pour des photos/scans). Les autres encodages PDF (captures d'écran ou schémas exportés depuis Word/PowerPoint, par exemple) sont silencieusement ignorés — leur décodage n'est pas pris en charge par la librairie utilisée.
- L'IA ne « voit » pas les images : elle ne peut donc pas les placer près du paragraphe pertinent. Elles sont ajoutées **à la fin** — de la leçon (leçon unique), ou de la première leçon du premier chapitre (formation complète). À repositionner manuellement si besoin dans l'éditeur.
- Maximum 10 images par document.

### Limites à connaître

- **Quota formateur** : 3 générations IA par jour et par formateur (leçon depuis document + formation complète partagent le compteur `texte` ; le quiz et l'audio utilisent leurs compteurs de type séparés, avec la même limite).
- **Quota admin plateforme** : 20 générations par jour et par type par défaut, partagées entre les comptes admin et configurables avec `MISTRAL_ADMIN_DAILY_GENERATION_LIMIT`.
- **Modération de contenu** : un thème ou document manifestement inapproprié (violence, haine, contenu sexuel, etc.) est refusé immédiatement, sans consommer le quota.
- Le contenu généré doit être relu : l'IA peut se tromper ou mal interpréter un document mal structuré.

### Générer l'audio d'une leçon

Depuis l'écran d'édition d'une leçon (`/formateur/mes-modules/lectures/{id}/edition`), bouton **« Générer l'audio »** en haut de page (sauf pour les leçons SCORM/slides, verrouillées). Le texte de tous les blocs `text` et `quote` de la leçon est lu à voix haute (voix française), produisant un unique fichier audio écoutable directement sur la page — et automatiquement visible aussi côté stagiaire, en haut de la leçon.

- Le bouton devient **« Régénérer l'audio »** une fois un audio déjà généré : la nouvelle version remplace l'ancienne (un seul fichier audio par leçon).
- Si la leçon ne contient aucun bloc `text`/`quote` avec du texte réel (uniquement image, vidéo, SCORM...), la génération est refusée avec un message explicite.
- Contrairement aux deux autres générations, celle-ci **ne passe pas par la modération** : elle lit un contenu déjà rédigé et revu par le formateur (ou déjà passé par la modération s'il vient d'une génération IA), pas un nouveau prompt libre.

---

## Partie technique

### Configuration

| Variable | Rôle | Requis |
|----------|------|--------|
| `MISTRAL_API_KEY` | Clé API Mistral | Oui (fonctionnalité désactivée sans clé) |
| `MISTRAL_MODEL` | Modèle de chat utilisé | Non (défaut : `mistral-large-latest`) |
| `MISTRAL_MONTHLY_TOKEN_LIMIT` | Budget mensuel d'un formateur | Non (défaut : 500 000 tokens) |
| `MISTRAL_ADMIN_MONTHLY_TOKEN_LIMIT` | Budget mensuel partagé de la plateforme admin | Non (défaut : 2 000 000 tokens) |
| `MISTRAL_ADMIN_DAILY_GENERATION_LIMIT` | Limite quotidienne admin, par type et partagée entre admins | Non (défaut : 20) |
| `PIPER_BINARY_PATH` | Chemin vers l'exécutable `piper` | Non (défaut : `storage/app/piper/piper/piper`) |
| `PIPER_MODEL_PATH` | Chemin vers le modèle de voix `.onnx` | Non (défaut : `storage/app/piper/voices/fr_FR-siwis-medium.onnx`) |

Lues via `config('services.mistral.*')` et `config('services.piper.*')` (`config/services.php`). La modération utilise toujours le modèle `mistral-moderation-latest`, non configurable.

**Piper n'est pas un package Composer** : c'est un binaire natif (~25 Mo) + un modèle de voix (~60 Mo) à installer séparément sur **chaque serveur** (dev et production), contrairement à Mistral qui n'est qu'un appel API. Installation utilisée en dev :
```bash
mkdir -p storage/app/piper/voices
curl -sL https://github.com/rhasspy/piper/releases/download/2023.11.14-2/piper_linux_x86_64.tar.gz | tar -xz -C storage/app/piper
curl -sL -o storage/app/piper/voices/fr_FR-siwis-medium.onnx \
  https://huggingface.co/rhasspy/piper-voices/resolve/main/fr/fr_FR/siwis/medium/fr_FR-siwis-medium.onnx
curl -sL -o storage/app/piper/voices/fr_FR-siwis-medium.onnx.json \
  https://huggingface.co/rhasspy/piper-voices/resolve/main/fr/fr_FR/siwis/medium/fr_FR-siwis-medium.onnx.json
```
`storage/app/piper/` est exclu de git (comme tout `storage/app/*`) — à réinstaller sur chaque environnement.

### Composants (`app/Domains/ModulesFormateur/`)

| Fichier | Rôle |
|---------|------|
| `Support/MistralClient.php` | Appels HTTP bruts à l'API Mistral : `chat()` (complétion JSON) et `moderate()` (modération). Journalise la conso token de chaque appel (voir plus bas) |
| `Support/ExtracteurTexteDocument.php` | Extraction de texte (`extract()`) et d'images (`extractImages()`) : `smalot/pdfparser` (PDF), `phpoffice/phpword`/`ZipArchive` (.docx), lecture directe (.txt) |
| `Support/GardeFouPromptIA.php` | Vérifie le thème/document via `MistralClient::moderate()`, lève une exception si contenu bloquant |
| `Support/LimiteurGenerationIA.php` | Quotas journaliers par rôle via `RateLimiter` : compteurs personnels formateur et compteurs plateforme admin |
| `Support/LimiteurBudgetTokensIA.php` | Budget mensuel personnel formateur ou budget mensuel partagé entre comptes admin |
| `Actions/GenererLeconIA.php` | Orchestration : document → texte → garde-fous → prompt → blocs de leçon → images |
| `Actions/GenererStructureFormationIA.php` | Orchestration : thème/document → texte → garde-fous → prompt → module + chapitres + leçons → images |
| `Actions/ImporterImagesDocument.php` | Extrait les images d'un document et les rattache au module (`TeleverserImageModule`), renvoie des blocs `image` prêts à insérer |
| `Support/PiperTtsClient.php` | Shell-out vers le binaire `piper` (`Symfony\Process`), texte en entrée (stdin), fichier WAV en sortie |
| `Actions/GenererAudioLecon.php` | Orchestration : leçon → texte lisible (blocs `text`/`quote`) → quota → Piper → media `lesson-audio` sur la leçon |

### Flux — génération de leçon

1. `ModuleBuilderController::generateLectureIA()` (route `formateur.modules.builder.lectures.generate-ia`, POST `sections/{section}/lectures/generer-ia`) valide le fichier et délègue à `GenererLeconIA::execute()`, avec le `Module` du chapitre ciblé.
2. `ExtracteurTexteDocument` extrait le texte brut du document.
3. `MistralClient::chat()` appelle l'API Mistral en mode JSON strict, avec un prompt qui structure le document en blocs `text` pédagogiques et suggère un titre.
4. `ImporterImagesDocument` extrait les images du même document, les rattache au module (`TeleverserImageModule`, collection `lesson-images`) et ajoute un bloc `image` par image trouvée, à la fin des blocs générés.
5. L'ensemble des blocs passe par le `NettoyeurBlocsModule` existant (même sanitizer que l'éditeur manuel) avant d'être persisté via `CreerLecon`, dans le chapitre choisi.

### Flux — génération de formation complète

1. `ModuleBuilderController::generateStructureIA()` côté formateur ou `ConstructeurFormationController::genererStructureIA()` côté admin valide thème/document et appelle `GenererStructureFormationIA::execute()`. Une limite d'exécution étendue est posée avant l'appel : la génération complète peut prendre 1 à 4 minutes.
2. Si un document est fourni, `ExtracteurTexteDocument` en extrait le texte. Le thème et/ou le texte du document sont combinés dans le prompt utilisateur envoyé à Mistral.
3. `MistralClient::chat()` est appelé avec un timeout de 260 s et jusqu'à 12000 tokens de réponse, en mode JSON strict. Le prompt demande 3 à 5 chapitres de 2 à 4 leçons, chaque leçon avec un contenu déjà rédigé (blocs `text`), ainsi qu'un tableau `objectifs` (3 à 5 chaînes). **Historique** : ce plafond était initialement à 8000 tokens ; des générations volumineuses tronquaient la réponse JSON (`completion_tokens` pile à la limite), rendant le JSON invalide et faisant échouer silencieusement la génération (redirection `back()` vers une page sans rapport, faute d'URL précédente utile en session) — relevé et corrigé après plusieurs échecs reproductibles en test.
4. `$decoded['objectifs']` est nettoyé (`strip_tags`, limite 255 car/objectif, 8 objectifs max) puis passé à `creerModuleVide()`, qui l'enregistre dans `Module::objectifs` (colonne cast `array`). Le module est créé via `CreerModule::creerModuleVide()` (variante de `CreerModule::execute()` sans la structure d'exemple). Si un document est fourni, `ImporterImagesDocument` en extrait les images et les rattache au module, **avant** la création des chapitres/leçons.
5. Chapitres et leçons sont créés dans une transaction (`CreerChapitre`, `CreerLecon` — même sanitizer que le reste du builder). Les blocs `image` extraits à l'étape précédente sont ajoutés à la fin des blocs de la **première leçon du premier chapitre**.
6. Redirection vers le plan du module généré.

### Flux — génération audio d'une leçon

1. `ModuleBuilderController::generateAudioLecture()` (route `formateur.modules.builder.lectures.generate-audio`, POST `lectures/{lecture}/generer-audio`) délègue à `GenererAudioLecon::execute()`.
2. Le quota `audio` est vérifié (`LimiteurGenerationIA::tropDeTentatives($trainerId, 'audio')` — compteur indépendant du quota `texte`).
3. `GenererAudioLecon::extraireTexteLisible()` parcourt `content_blocks`, ne garde que les blocs `text` (HTML nettoyé via `strip_tags`) et `quote`, concatène le tout. Si le résultat est vide (leçon uniquement image/vidéo/SCORM), `RuntimeException` immédiate.
4. `PiperTtsClient::synthesize()` lance le binaire Piper (texte passé sur `stdin`, fichier `.wav` temporaire en sortie), retourne les octets audio.
5. `ModuleLecture::addMediaFromString()` stocke le WAV dans la collection `lesson-audio` (`singleFile()` : toute régénération remplace le fichier précédent, pas d'accumulation).

Aucune modération n'est appliquée ici : le texte lu existe déjà dans la leçon (rédigé ou validé par le formateur), ce n'est pas un nouveau prompt libre.

Point d'attention : `Illuminate\Http\Client\ConnectionException` (timeout réseau vers Mistral) n'hérite pas de `RuntimeException` — `generateStructureIA()` et `generateLectureIA()` catchent donc `ConnectionException` séparément (message générique « a pris trop de temps ») de `RuntimeException` (message spécifique : quota, modération, réponse invalide), sinon un timeout Mistral remonte en erreur 500 au lieu d'un message utilisateur.

### Garde-fous : modération et quota

Les actions IA concernées appliquent systématiquement, dans cet ordre, avant tout appel de génération coûteux :

1. **Quota** (`LimiteurGenerationIA`) : vérifie via `RateLimiter::tooManyAttempts()` la limite du rôle, dans une fenêtre de 24 h. Pour un formateur, la clé `ia-generation-formateur:{type}:{id}` et la limite de 3 sont personnelles. Pour l'administration, la clé `ia-generation-admin:{type}` est commune aux comptes admin et la limite vient de `MISTRAL_ADMIN_DAILY_GENERATION_LIMIT` (20 par défaut). Les types `texte`, `quiz` et `audio` ont des compteurs indépendants. Si la limite est dépassée, une `RuntimeException` est levée avant tout appel réseau.
2. **Budget mensuel** (`LimiteurBudgetTokensIA`) : compare la somme des `total_tokens` du mois en cours au plafond correspondant. Un formateur est limité par `MISTRAL_MONTHLY_TOKEN_LIMIT` (500 000 tokens par défaut). L'administration agrège tous les enregistrements dont l'acteur a le rôle `admin` et applique `MISTRAL_ADMIN_MONTHLY_TOKEN_LIMIT` (2 000 000 tokens par défaut). Les deux enveloppes sont séparées. Si le plafond est atteint, le blocage est immédiat ; le compteur repart avec le mois suivant, à partir de `created_at`.
3. **Modération** (`GardeFouPromptIA` + `MistralClient::moderate()`) : envoie le thème et/ou le texte extrait du document à l'endpoint `POST /v1/moderations` (modèle `mistral-moderation-latest`). Catégories bloquantes : `sexual`, `hate_and_discrimination`, `violence_and_threats`, `dangerous`, `criminal`, `selfharm`, `jailbreaking`. **Volontairement exclues** : `health`, `financial`, `law`, `pii` — une plateforme de formation professionnelle traite légitimement ces sujets (premiers secours, gestion budgétaire, droit du travail, protection des données), les inclure aurait généré trop de faux positifs. Rejet immédiat (`RuntimeException` avec le nom des catégories déclenchées) si une catégorie bloquante est détectée à `true`.
4. Seulement si la modération passe, `LimiteurGenerationIA::enregistrerTentative()` incrémente le compteur de quota — **juste avant** l'appel de génération proprement dit, donc un contenu rejeté par la modération ne consomme pas de quota, mais un timeout Mistral (après modération) en consomme un (l'appel a réellement eu lieu).

La page « Ma consommation IA » du formateur (`/formateur/mes-modules/consommation-ia`) affiche sa consommation personnelle. La vue équivalente du constructeur admin agrège les appels de tous les comptes admin et affiche la progression du budget plateforme ; l'association d'un référent à une formation n'affecte pas cette attribution.

`GenererLeconIA::execute()` et `GenererStructureFormationIA::execute()` prennent donc un paramètre `int $trainerId` en plus du document/thème.

### Suivi de la consommation de tokens

Chaque appel Mistral (`chat()` et `moderate()`) est tracé à deux endroits, dans `MistralClient::logUsage()` :

1. **Log fichier** : une ligne dans le canal dédié `mistral` (`config/logging.php`, driver `daily`, fichier `storage/logs/mistral-{date}.log`, conservé 30 jours) — volontairement séparé de `laravel.log` pour rester discret. Sert de complément brut au dashboard de facturation Mistral (console.mistral.ai).
2. **Table `consommations_ia`** (modèle `ConsommationIA`) : un enregistrement durable par appel — `formateur_id`, `type` (`chat`/`moderate`), `model`, `prompt_tokens`, `completion_tokens`, `total_tokens`. C'est la source de données des deux tableaux de bord de suivi (le log fichier n'a pas de rétention longue et n'est pas exploitable en UI).

Deux vues exploitent `ConsommationIADashboardService` (`app/Services/ConsommationIADashboardService.php`) :
- **Admin** — `/admin/pilotage/consommation-ia` (`admin.pilotage.consommation-ia`, `ConsommationIAController`) : total de tokens et de générations par formateur, tous formateurs confondus, triés par consommation décroissante.
- **Constructeur admin** — `/admin/formations/constructeur/consommation-ia` : totaux, historique et budget mensuel agrégés pour les acteurs ayant le rôle `admin`.
- **Formateur** — `/formateur/mes-modules/consommation-ia` (`formateur.modules.builder.consommation-ia`, `ModuleBuilderController::consommationIA()`) : ses propres totaux (générations, tokens prompt/réponse) + historique paginé de ses générations. Lien accessible depuis l'en-tête de la page « Mes créations ».

Volontairement limité aux tokens bruts, sans estimation de coût en euros : les tarifs Mistral varient selon le modèle et changent dans le temps, un coût affiché deviendrait vite trompeur sans mise à jour manuelle régulière.

### Extraction d'images des documents (détail technique)

`ExtracteurTexteDocument::extractImages()` (max 10 images par document, silencieusement tronqué au-delà) :
- **PDF** : parcourt les `XObject` de chaque page via `smalot/pdfparser`, filtre les `Smalot\PdfParser\XObject\Image` dont le `Filter` vaut `DCTDecode` (JPEG) — les autres filtres (`FlateDecode` brut, `CCITTFaxDecode`, `JPXDecode`...) ne sont pas décodés par la librairie et sont ignorés. Un même XObject peut apparaître deux fois dans `Page::getXObjects()` (id brut + id numérique nettoyé) : dédoublonné via `spl_object_id()`.
- **Word (.docx)** : un `.docx` est une archive ZIP ; les images sont lues directement via `ZipArchive` sous `word/media/*`, filtrées par extension (`png`, `jpg`, `jpeg`, `gif`, `webp`).
- Chaque image extraite est écrite dans un fichier temporaire (`tempnam()`), puis `ImporterImagesDocument` l'enveloppe dans un `UploadedFile` (mode test) pour réutiliser `TeleverserImageModule::execute()` tel quel. Le fichier temporaire est supprimé après upload (`finally`), que l'upload réussisse ou non.

### Dépendances Composer

| Package | Usage |
|---------|-------|
| `smalot/pdfparser` | Extraction de texte et d'images PDF |
| `phpoffice/phpword` | Extraction de texte Word `.docx` (les images `.docx` sont lues séparément via `ZipArchive`, sans passer par PhpWord) |
