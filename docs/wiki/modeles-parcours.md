# Modèles globaux de parcours

Les modèles globaux de parcours permettent à l’administration de préparer une structure officielle du catalogue Oneduc, puis aux formateurs d’en créer une copie personnelle.

> **État d'intégration :** le catalogue, le cycle de vie, la validation des configurations et la duplication structurelle sont en place. La matérialisation des étapes génériques en sessions d'outils exécutables n'est pas encore implémentée ; elle ne doit pas être présentée comme disponible aux stagiaires.

## Cycle de vie

Un modèle possède l’un des statuts suivants :

- `brouillon` : modifiable et supprimable par l’administration ;
- `publie` : immuable et visible dans le catalogue formateur ;
- `archive` : immuable et retiré du catalogue, sans effet sur les copies déjà créées.

La création produit toujours un brouillon. La publication est une action explicite. Pour faire évoluer un modèle publié ou archivé, l’administration crée une nouvelle version brouillon par duplication.

Le formateur ne modifie jamais le modèle global. Il duplique un modèle publié dans son espace « Mes parcours » ; la copie lui appartient, reste liée à sa provenance par `modele_parcours_id`, et n'est pas modifiée si le modèle source est ensuite archivé.

## Structure des données

Les tables `modeles_parcours` et `modele_parcours_items` stockent respectivement l’en-tête et les étapes ordonnées.

Une étape est de l’un des types génériques suivants :

- `module` : référence explicite vers une version publiée et assignable d'une formation officielle avec `module_id` ;
- `outil` : clé stable du registre dans `outil` et configuration pédagogique validée dans `configuration` (JSON).

Lorsqu’un formateur copie un modèle, un `FormateurParcours` lui appartenant est créé. La colonne `formateur_parcours.modele_parcours_id` conserve la provenance. Les étapes génériques sont copiées dans `formateur_parcours_items`.

## Registre des outils

`App\Support\Parcours\RegistreOutilsParcours` est la source de vérité pour les outils utilisables dans un modèle. Il couvre les outils pédagogiques ou collaboratifs exposés dans le dépôt :

- nuage de mots ;
- sondage ;
- quiz en direct ;
- tableau blanc ;
- mur de questions ;
- vrai ou faux ;
- buzzer ;
- échelle de positionnement ;
- zone de clic ;
- roue aléatoire ;
- minuteur ;
- émargement ;
- page collaborative HedgeDoc ;
- pendu ;
- jeu de mémoire ;
- carrousel ;
- cartes à retourner ;
- cartes à trier.

Les outils conditionnés par `config('outils.*.enabled')` ne peuvent être ajoutés ou dupliqués que lorsqu’ils sont activés. La page collaborative nécessite également une URL HedgeDoc configurée.

Le constructeur de modules, l’import PowerPoint et la banque de questions ne sont pas des étapes d’outil : les formations produites sont ajoutées au parcours avec le type `module`.

## Protection des données runtime

Un modèle décrit une activité ; il ne représente jamais une session lancée.

Le registre refuse notamment les clés de configuration relatives :

- aux codes d’accès ;
- aux participants ou utilisateurs ;
- aux réponses et entrées ;
- aux résultats, votes et scores ;
- aux identifiants de session, groupe ou formateur.

L’action `DupliquerModeleParcours` ne crée aucune ligne dans les tables de sessions d’outils. Elle copie uniquement la structure validée. Les réponses et résultats de sessions existantes ne sont donc jamais dupliqués.

## Limite runtime actuelle

Le socle enregistre et duplique tous les types d’outils, mais ne matérialise pas encore leur configuration dans une session exécutable liée à un groupe. Le lancement devra être implémenté outil par outil dans un lot ultérieur afin de générer un nouveau code d’accès et une session vide au moment opportun.

Tant que cette matérialisation n’est pas disponible, une copie contenant un type générique `outil` doit être considérée comme une structure de préparation. Les anciens types `wordcloud` et `poll` des parcours formateur restent pris en charge par leur flux historique.

## Routes

Les routes sont regroupées dans `routes/admin-modeles-parcours.php`, inclus par `routes/web.php`, avec deux frontières de sécurité :

- administration : `auth`, `role:admin`, `admin.activity` ;
- formateur : `auth`, `role:formateur`, `association.member`.

Les noms de routes utilisent respectivement `admin.modeles-parcours.*` et `formateur.modeles-parcours.*`.

---

[Retour à Groupes & Parcours](06-groupes-parcours.md) · [Retour au wiki](README.md)
