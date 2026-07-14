# Quiz en direct

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Le quiz en direct rejoue, en mode synchronisé, les questions déjà rédigées dans la banque de questions d'une leçon. Les résultats de la session live sont indépendants de la progression pédagogique : ils ne modifient pas le score du quiz de fin de leçon que le stagiaire repassera en autonomie.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone — le formateur avance question par question |
| Lieu | Présentiel (écran de projection) ou distanciel (partage d'écran) |
| Participation | Collective, dans un groupe |
| Compte requis | Oui (élève rattaché au groupe) |
| Contenu source | Banque de questions d'une leçon précise |
| Activable/désactivable | Toujours actif, pas de variable d'environnement dédiée |

## Comment ça marche

1. Le formateur ouvre `/formateur/quiz-en-direct` : la page liste ses formations, avec pour chaque leçon le nombre de questions actives dans sa banque.
2. Il choisit une leçon et un groupe, puis lance une session. Un code d'accès court est généré.
3. Les stagiaires rejoignent depuis `/stagiaire/live-quiz` en saisissant le code, ou via un lien direct.
4. Le formateur avance manuellement question par question (`current_position`) ; les réponses des stagiaires remontent en direct, puis il révèle la bonne réponse avant de passer à la suivante.
5. La session se termine (`ended_at`) : les résultats restent consultables mais n'affectent ni la progression du module, ni le quiz individuel de fin de leçon.

## Dans quel contexte l'utiliser

- Réviser collectivement une notion juste après l'avoir enseignée, à chaud, en gardant tout le monde synchronisé sur la même question.
- Créer un temps fort compétitif ou ludique en fin de séquence sans avoir à ressaisir des questions : on réutilise directement la banque déjà rédigée pour la leçon.
- Fonctionne aussi bien avec un vidéoprojecteur en salle qu'en partage d'écran à distance, tant que le formateur garde le contrôle du rythme.

## Bon à savoir

- Le lancement du quiz en direct reste dans l'espace **Outils** — il n'y a volontairement pas de bouton de lancement directement dans la page d'une leçon, même si c'est là que la banque de questions est rédigée.
- Comme les autres activités live, les résultats s'affichent côté formateur avec un délai de 2 à 3 secondes (polling, pas de WebSocket).

## Partie technique

**Routes formateur** (`routes/formateur.php`) : `GET /formateur/quiz-en-direct` (`OutilsLiveQuizController@index`, nom `formateur.outils.quiz.index`).

**Routes stagiaire** (`routes/stagiaire.php`, préfixe `/stagiaire/live-quiz`) : `index`, `lookup`, `notification-status`, `join/{code}`, `sessions/{session}/join`, `sessions/{session}` (show), `sessions/{session}/answer`, `sessions/{session}/snapshot` — contrôleur `Stagiaire\LiveQuizSessionController`.

**Modèles / tables** : `LiveQuizSession` (`live_quiz_sessions` : `formateur_id`, `group_id` nullable, `module_id`, `section_id`, `lecture_id`, `access_code`, `status`, `current_position`, `total_questions`, `answer_revealed_at`, `started_at`, `ended_at`), `live_quiz_session_questions` (snapshot ordonné des questions piochées dans `quiz_questions`), `live_quiz_session_participants` (unique par session+utilisateur, relié à un `quiz_attempts.id`).

**Gestion de la banque de questions** : `FormateurQuizQuestionController` (par leçon) et `ModuleQuizBankController` (vue unifiée arborescence + déplacement entre leçons — voir mémoire [[projet_banque_questions_vue_unifiee]]). Rappel : `QuizQuestion` n'a pas de colonne `position`/`points` en base malgré le modèle.

---

[Retour au sommaire des outils](../07-outils-animation.md)
