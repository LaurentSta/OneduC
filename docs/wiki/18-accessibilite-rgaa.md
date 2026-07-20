# 18 — Accessibilité et démarche RGAA

*Public : utilisateurs, contributeurs, développeurs et partenaires.*

## État publié

Depuis juillet 2026, Oneduc publie une première démarche d'accessibilité fondée sur le RGAA 4.1.2.

L'état affiché est volontairement prudent :

```text
Accessibilité : non conforme
```

Aucun audit RGAA complet et en cours de validité n'a encore établi de taux de conformité. La plateforme ne revendique donc ni conformité partielle, ni conformité totale.

Pages publiques :

- `/accessibilite` : déclaration d'accessibilité, difficultés déjà repérées, contact et voies de recours ;
- `/accessibilite/schema-pluriannuel` : stratégie 2026-2028 ;
- `/accessibilite/plan-action-2026` : actions, échéances prévisionnelles et indicateurs 2026.

Le lien « Accessibilité » est réservé à la colonne « Informations légales » du pied de page du site public, avec les mentions légales, la confidentialité, les conditions d'utilisation et la gestion des cookies. Le statut détaillé reste présenté dans la déclaration, où son contexte est expliqué. Aucun lien supplémentaire n'est ajouté aux tableaux de bord ou aux écrans applicatifs. Les trois pages figurent dans le sitemap XML.

## Améliorations livrées avec la publication

- lien « Aller au contenu principal » visible à la prise de focus sur les layouts public, admin, formateur, stagiaire et observateur ;
- cible de contenu principal focalisable sans supprimer l'identifiant d'animation `page-transition` ;
- focus visible ajouté au bouton du menu « Association » ;
- information bêta transformée en notification non bloquante afin de ne plus concurrencer la fenêtre de gestion des cookies ;
- version FALC migrée vers le composant de dialogue partagé, avec focus initial, navigation Tab contenue, fermeture avec Échap et restitution du focus ;
- lien « Accessibilité » intégré à la colonne des informations légales du pied de page.

Ces améliorations réduisent certains obstacles, mais ne constituent pas une preuve de conformité de l'ensemble du site.

## Plan 2026

| Action | Cible | État initial |
|--------|-------|--------------|
| Publier la déclaration, le schéma et le plan | Juillet 2026 | Mis en œuvre dans le code |
| Renforcer le socle de navigation publique | 2026 | En cours |
| Désigner un référent accessibilité | T3 2026 | Prévu |
| Inventorier les parcours, documents, médias et contenus SCORM | T3 2026 | Prévu |
| Réaliser un audit RGAA 4.1.2 représentatif | T4 2026 | Prévu |
| Corriger les obstacles critiques issus de l'audit | T4 2026 | Prévu |
| Encadrer la création de contenus accessibles | T4 2026 | Prévu |
| Installer une recette continue | T4 2026 | Prévu |
| Publier le bilan 2026 et préparer le plan 2027 | Fin 2026 | Prévu |

Les dates restent prévisionnelles et doivent être ajustées selon les ressources disponibles et les résultats de l'audit.

## Périmètre du futur audit

L'échantillon devra représenter :

- l'accueil, le contact, l'inscription et l'authentification ;
- les espaces admin, formateur, stagiaire et observateur ;
- les constructeurs et lecteurs de modules ;
- les quiz, outils collaboratifs et activités en direct ;
- les vidéos, graphiques, canvas, documents et contenus SCORM ;
- les erreurs de saisie, messages dynamiques et changements de contexte.

La vérification associera les critères RGAA applicables, une navigation intégrale au clavier, le zoom et la redistribution du contenu, des lecteurs d'écran et, lorsque possible, des tests avec des personnes en situation de handicap. Les outils automatiques complètent cette recette mais ne calculent pas seuls la conformité.

## Règles de publication

Ne pas publier sans preuve :

- un taux de conformité ;
- les mentions « partiellement conforme » ou « totalement conforme » ;
- une dérogation pour charge disproportionnée ;
- une exemption supposée pour un contenu tiers ou SCORM ;
- une liste de non-conformités présentée comme exhaustive avant l'audit.

La déclaration devra être mise à jour après l'audit, après une modification substantielle du service et lors des échéances prévues par le référentiel applicable.

Le schéma doit également tenir à jour la gouvernance, les ressources humaines et financières effectivement affectées, les compétences attendues, les expertises externes et les exigences contractuelles. Tant que ces moyens ne sont pas validés, ils sont présentés comme restant à chiffrer ou à décider.

## Références

- [Référentiel RGAA](https://accessibilite.numerique.gouv.fr/)
- [Déclaration d'accessibilité](https://accessibilite.numerique.gouv.fr/obligations/declaration-accessibilite/)
- [Schéma pluriannuel](https://accessibilite.numerique.gouv.fr/obligations/schema-pluriannuel/)
- [Méthode d'évaluation](https://accessibilite.numerique.gouv.fr/obligations/evaluation-conformite/)

---

[Retour au wiki](README.md)
