# Politique de sécurité

La sécurité d'Oneduc est prioritaire, car la plateforme traite des données d'apprentissage, des comptes utilisateurs, des groupes de formation et des résultats pédagogiques.

## Signaler une faille

Ne publiez pas une faille de sécurité exploitable dans une Issue publique GitHub.

Envoyez le signalement à : contact@oneduc.fr

Objet conseillé :

```text
[SECURITY] Signalement de faille Oneduc
```

Inclure si possible :

- une description claire du problème ;
- les étapes de reproduction ;
- l'impact potentiel ;
- les fichiers, routes ou endpoints concernés ;
- la version ou le commit testé ;
- toute preuve utile sans exposer de données personnelles réelles.

## Périmètre prioritaire

Les signalements les plus sensibles concernent notamment :

- contournement d'authentification ;
- accès à un rôle non autorisé ;
- accès à des modules non affectés à un stagiaire ;
- fuite de données personnelles ;
- modification non autorisée de progression ou de score ;
- injection SQL ;
- exécution de code ;
- faille liée à l'import SCORM ;
- exposition de fichiers `.env`, secrets, dumps SQL ou clés privées.

## Versions prises en charge

Tant que le projet n'a pas publié de version stable, seule la branche principale maintenue officiellement par l'Association Oneduc est prise en charge.

| Version | Support sécurité |
|---------|------------------|
| Branche principale maintenue | Oui |
| Forks non maintenus | Non |
| Anciennes branches expérimentales | Non |

## Règles de divulgation responsable

Merci de :

- laisser un délai raisonnable à l'équipe pour analyser et corriger ;
- ne pas exploiter la faille sur des données réelles ;
- ne pas exfiltrer de données ;
- ne pas publier de preuve d'exploitation tant qu'une correction n'est pas disponible ;
- limiter les tests au strict nécessaire.

## État connu avant publication publique

La documentation du projet identifie déjà plusieurs corrections bloquantes avant publication publique :

- routes admin de debug ou de reset à sécuriser ;
- contrôle d'accès aux modules à renforcer ;
- throttling à ajouter sur la connexion par code ;
- flux de retour de leçon à corriger ;
- vérification de l'historique Git pour éviter toute fuite de secrets ou de données personnelles.

Ces points doivent être corrigés avant de rendre le dépôt public.
