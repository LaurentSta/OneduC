# Nuage de mots

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Le formateur pose une question ouverte, les stagiaires soumettent un ou plusieurs mots, et le nuage se construit en direct : plus un mot revient souvent, plus il apparaît gros. Utile pour faire émerger les représentations initiales d'un groupe avant d'entamer une notion.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Asynchrone côté saisie (les mots arrivent au fil de l'eau) |
| Lieu | Présentiel (projection) ou distanciel |
| Participation | Individuelle en saisie, restitution collective |
| Compte requis | Non pour la session autonome (voir plus bas) |
| Deux modes | Outil autonome (par groupe) ou item inséré dans un parcours |
| Activable/désactivable | Toujours actif |

## Comment ça marche

Le formateur peut l'utiliser de deux façons :

- **Outil autonome** : depuis `/formateur/nuages-de-mots`, il choisit un groupe, pose une question et ouvre une session active avec un code d'accès. Il peut faire évoluer la question en cours de session (`setQuestion`) et fermer la session quand il le souhaite (`close`).
- **Item de parcours** : le nuage de mots est intégré comme étape d'un parcours ; les stagiaires y répondent en autonomie au fil de leur progression.

Côté stagiaire, l'accès reste centralisé sur la carte "Nuage de mots" de `/stagiaire/outils` : elle pointe vers la session active du groupe ou, à défaut, vers le nuage du parcours en cours. La vue stagiaire ne montre que le formulaire de saisie — jamais le nuage en train de se construire, qui reste réservé à la projection formateur (vue live).

Quand un nuage autonome s'ouvre pour son groupe, le stagiaire reçoit un message dans `/stagiaire/messages` et une notification sur la cloche, avec le code de participation directement affiché dans l'alerte.

## Dans quel contexte l'utiliser

- En ouverture de séquence, pour recueillir les représentations initiales d'un groupe sur un thème ("Pour vous, qu'est-ce que le RGPD ?").
- En clôture, pour faire un rapide bilan collectif des notions retenues.
- Intégré à un parcours, comme point de passage asynchrone que chaque stagiaire complète à son rythme, sans que le formateur ait besoin d'être présent.

## Bon à savoir

- Depuis le 14 juillet 2026, le nuage de mots autonome exige une authentification et une appartenance au groupe actif, au même titre que les 5 autres outils live (Sondage, Mur de questions, Quiz live, Tableau blanc, Minuteur) — voir [10-securite-rgpd.md](../10-securite-rgpd.md). Un visiteur non connecté ou hors groupe est redirigé/rejeté même en connaissant le code d'accès.
- Supprimer un nuage autonome retire le nuage et ses réponses, sans toucher au groupe ni aux comptes stagiaires.

## Partie technique

**Routes formateur** (`routes/formateur.php`, préfixe `/formateur/nuages-de-mots`, nom `formateur.nuages.`) : `index`, `store`, `{wordCloud}/live`, `{wordCloud}/question`, `{wordCloud}/close`, `{wordCloud}/live/data`, `destroy` (`FormateurWordCloudController`). Vue live par groupe côté parcours : `formateur.groupes.wordcloud.live` / `.data` (`GroupeWordCloudController`).

**Routes de participation** (`routes/web.php`, préfixe `/oneduc/mot`, **sous middleware `auth`**) : `home`, `resolveCode`, `join/{code}`, `submit`, `state`, `live.data` (`WordCloudParticipationController`).

**Routes stagiaire (parcours)** (`routes/stagiaire.php`, préfixe `/wordcloud`) : `notification-status`, `parcours.show`, `parcours.submit`, `parcours.data` (`ParcoursWordCloudController` / `WordCloudController`).

**Modèles** : `WordCloud` (session autonome, `access_code`, `questions_array`, `active_question_index`) + ses réponses. L'agrégateur stagiaire (`StagiaireController::StagiaireOutils()`) additionne les sessions autonomes du groupe et les items `wordcloud` du parcours actif ; il privilégie la session autonome active puis bascule vers le premier item de parcours.

---

[Retour au sommaire des outils](../07-outils-animation.md)
