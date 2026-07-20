# 09 — Design system

*Public : développeurs et designers.*

Le design system d'Oneduc est défini dans `tailwind.config.js`. Il étend Tailwind CSS v4 avec des tokens de couleur, de typographie et des composants réutilisables.

---

## Couleurs

### Palette principale

| Token | Valeur | Usage |
|-------|--------|-------|
| `bleuone` | `#004461` | Couleur primaire : titres, fond de navigation, boutons principaux |
| `bleuone-light` | `#005d85` | Variante claire du bleu (hover, accents) |
| `bleuone-dark` | `#002c3f` | Variante sombre (textes foncés, backgrounds) |
| `orangeone` | `#E94D2A` | CTAs, boutons d'action, mises en valeur |
| `orangeone-hover` | `#c43d1f` | État hover du orange |
| `orangeone-light` | `#ff7a5c` | Variante claire (badges, tags) |
| `vertone` | `#01c69c` | Succès, états positifs, complétion, validation |

### Ombres

| Token | Valeur | Usage |
|-------|--------|-------|
| `shadow-soft` | `0 10px 30px -10px rgba(0, 68, 97, 0.1)` | Ombre douce sur les cartes |
| `shadow-card` | `0 4px 6px -1px rgba(0, 0, 0, 0.1)` | Ombre standard sur les éléments |

---

## Typographie

### Familles de polices

| Token | Police | Usage |
|-------|--------|-------|
| `font-raleway` | Raleway | Titres de page, grands titres |
| `font-varela` | Varela Round | Sous-titres, boutons, éléments d'interface |
| `font-lisible` | Arial (fallback, remplace OpenDyslexic si non chargée) | Corps de texte accessible |

> **Note** : `font-lisible` est défini comme `Arial` dans la config actuelle. Si OpenDyslexic est chargée via CSS, elle peut remplacer Arial pour améliorer la lisibilité pour les personnes dyslexiques.

### Tailles de texte

| Token | Taille | Line-height | Font-weight | Usage |
|-------|--------|-------------|-------------|-------|
| `text-titre` | 55px | 1.1 | 500 | Titre principal de page |
| `text-sous-titre` | 28px | 1.3 | 600 | Sous-titre de section |

---

## Composants Tailwind personnalisés

### Boutons

Définis comme composants Tailwind via `plugin(addComponents)` dans `tailwind.config.js`.

#### `.btn-oneduc` — CTA principal (orange rempli)

```html
<button class="btn-oneduc">Commencer la formation</button>
```

Styles : fond orange, texte blanc, bord orange, arrondi complet, effet hover (fond blanc, texte orange), focus ring, animation scale au clic.

#### `.btn-oneduc-outline` — Bouton secondaire (contour bleu)

```html
<button class="btn-oneduc-outline">Voir les détails</button>
```

Styles : fond blanc, texte bleu, bord bleu, effet hover (fond bleu, texte blanc).

#### `.btn-oneduc-blue` — Bouton primaire bleu

```html
<button class="btn-oneduc-blue">Enregistrer</button>
```

Styles : fond bleu, texte blanc, bord bleu, effet hover (fond blanc, texte bleu).

#### `.btn-oneduc-danger` — Action destructive (rouge)

```html
<button class="btn-oneduc-danger">Supprimer</button>
```

Styles : fond rouge, texte blanc, bord rouge, effet hover (fond blanc, texte rouge).

---

### Texte `.prose-oneduc`

```html
<div class="prose-oneduc">
  <p>Contenu pédagogique...</p>
</div>
```

Styles : police lisible, 18px, line-height 1.8, espacement entre paragraphes. Conçu pour le corps de texte pédagogique.

---

### Carte `.card-feature`

```html
<div class="card-feature">
  <h3>Titre de la carte</h3>
  <p>...</p>
</div>
```

Styles : fond blanc, padding 8, arrondi 3xl, ombre douce, bord transparent au repos, bord vert + translation vers le haut au hover, transition douce.

---

## Patterns de cartes des dashboards

### Formateur, stagiaire et observateur

Le pattern historique reste utilisé dans les tableaux de bord formateur, stagiaire et observateur :

```html
<div class="bg-white rounded-[20px] shadow-md p-6">
  <!-- contenu -->
</div>
```

`rounded-[20px]` reste la valeur de référence pour ces espaces. Ne pas modifier ce pattern lors d'une intervention limitée à l'administration.

### Administration

L'administration utilise une variante plus dense et plus sobre :

```html
<section class="rounded-xl border border-slate-200 bg-white p-4">
  <!-- contenu administratif -->
</section>
```

Les cartes admin utilisent principalement un rayon de `12px` (`rounded-xl`), une bordure `slate-200` et des espacements de `12px` à `20px`. La hiérarchie visuelle repose d'abord sur les bordures, les fonds neutres et la typographie ; les ombres sont réservées aux éléments superposés comme les menus du header via `.admin-elevation`.

## Pattern d'en-tête de page

Les pages internes formateur et stagiaire utilisent un en-tête compact commun :

```html
<header class="rounded-[20px] border border-gray-100 bg-white shadow-md">
  <div class="grid gap-6 px-6 py-6 md:px-8 md:py-7 lg:grid-cols-12 lg:items-center">
    <div class="lg:col-span-8">
      <x-oneduc.breadcrumb :items="$items" />
      <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">Titre</h1>
      <p class="mt-0.5 font-varela text-base text-orangeone md:text-lg">Sous-titre</p>
      <p class="mt-3 max-w-2xl font-lisible text-sm leading-relaxed text-slate-700">Description courte.</p>
    </div>
  </div>
</header>
```

Les anciennes tailles `text-titre` et `text-sous-titre` restent disponibles pour les pages publiques ou les héros, mais ne doivent plus être utilisées pour les en-têtes courants des dashboards stagiaire, formateur et administrateur.

### En-tête de page administrateur

Les pages administrateur utilisent un en-tête sans carte englobante, séparé du contenu par une bordure basse :

```html
<header class="flex flex-col gap-4 border-b border-slate-200 pb-5 xl:flex-row xl:items-end xl:justify-between">
  <div>
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Section</p>
    <h1 class="!mb-1 !text-2xl !font-semibold text-slate-950">Titre</h1>
    <p class="text-sm text-slate-600">Description opérationnelle.</p>
  </div>
  <div class="flex flex-wrap gap-2"><!-- actions principales --></div>
</header>
```

La densité est obtenue en réduisant les espacements et les rayons, pas en supprimant les libellés ou en réduisant le corps de texte principal sous `14px`.

---

## Socle de l'administration

### Dimensions et comportement du shell

Le layout `resources/views/admin/admin_dashboard.blade.php` définit trois dimensions :

| Élément | Dimension | Comportement |
|---------|-----------|--------------|
| Header | `56px` (`3.5rem`) | Fixe en haut de l'écran |
| Sidebar dépliée | `248px` (`15.5rem`) | Affiche icônes, groupes et libellés |
| Sidebar repliée | `72px` (`4.5rem`) | Conserve les icônes et les infobulles natives des liens |

À partir du breakpoint `lg` (`1024px`), le bouton du header alterne entre les largeurs `248px` et `72px`. L'état est conservé dans `localStorage` sous la clé `admin-navigation-reduite`.

Sous `1024px`, la navigation devient un tiroir latéral :

- elle s'ouvre depuis le bouton du header ;
- un overlay masque le contenu en arrière-plan ;
- un clic sur l'overlay, le bouton de fermeture, un lien ou la touche Échap ferme le tiroir ;
- le défilement de la page est bloqué tant que le tiroir est ouvert.

La navigation est regroupée en quatre domaines :

- **Utilisateurs** : comptes, formateurs, stagiaires, observateurs et groupes ;
- **Pédagogie** : catégories, modules, évaluations, référentiels, compétences et badges ;
- **Pilotage** : projets et tâches, qualité des parcours, consommation IA, notifications, journal et retours ;
- **Outils** : outils collaboratifs actuellement exposés à l'administrateur.

Le header affiche le contexte courant, un accès à la création d'utilisateur, les notifications et le menu du compte. Alpine est chargé par Vite via `resources/js/app.js` ; le layout admin ne charge pas une seconde instance depuis un CDN.

### Tableaux, badges et actions

Les styles admin sont centralisés dans `resources/css/admin-tables.css`.

Deux classes de tableau coexistent :

- `.admin-table-dense` pour les nouvelles listes paginées côté serveur ;
- `.table-oneduc` pour les listes historiques encore pilotées par DataTables.

Leur présentation commune utilise un corps de `13px`, des en-têtes de `11px` en capitales, des cellules d'environ `10px × 12px`, des séparateurs discrets et un fond visible au survol ou lorsqu'un élément de la ligne reçoit le focus. Les nouvelles tables fournissent un `<caption>` pour les lecteurs d'écran et des en-têtes avec `scope="col"`.

Les badges compacts utilisent la classe `.admin-badge` et un libellé textuel visible :

| Variante | Usage actuel |
|----------|--------------|
| `.admin-badge--blue` | Rôle formateur |
| `.admin-badge--violet` | Rôle stagiaire |
| `.admin-badge--success` | Compte actif ou état positif |
| `.admin-badge--neutral` | Compte inactif ou état neutre |

Les actions secondaires d'une ligne utilisent `.admin-icon-button`, avec les variantes `--warning` et `--danger`. Chaque bouton doit conserver un `title` et un libellé `.sr-only` explicites ; l'icône seule n'est pas un nom accessible.

Dans l'interface admin, les CTA à texte blanc utilisent la nuance orange foncée `#c43d1f` (survol `#a8321a`). L'orange de marque `#E94D2A` reste disponible pour les accents, icônes et contours de focus, mais son contraste avec un texte blanc est insuffisant pour du texte courant.

### Filtres et formulaires

La barre de filtres de la gestion des utilisateurs suit ces règles :

- formulaire GET, afin que l'état soit partageable dans l'URL ;
- champs de `40px` de hauteur, texte de `14px` et rayon de `8px` ;
- recherche, rôle, statut, rattachement, tri et nombre de résultats par page ;
- bouton visible pour appliquer les filtres et bouton nommé pour les effacer ;
- pagination Laravel côté serveur.

Les formulaires admin historiques peuvent utiliser les classes `.form-oneduc-card`, `.form-oneduc-section`, `.form-oneduc-input`, `.form-oneduc-select`, `.form-oneduc-textarea` et `.btn-oneduc-sm`. Leur version actuelle adopte également des rayons de `8px` à `12px`, des champs de `40px` et un focus explicite.

---

## Transition de page (fondu)

Toutes les pages (front public, dashboards formateur/stagiaire/observateur/admin) partagent le même mécanisme de fondu à la navigation, centralisé dans `resources/js/app.js` (fonction `initPageTransitions`) et `resources/css/app.css` (règles `#page-transition`).

Fonctionnement :
- Chaque layout place le contenu de page dans un élément `#page-transition`. Dans le layout admin, cet élément est imbriqué dans `<main id="contenu-principal">` afin de fournir une cible distincte au lien d'évitement.
- Au chargement, `body.page-is-entering` fait apparaître le contenu en fondu (translateY + opacity, ~260ms).
- Au clic sur un lien interne, `body.page-is-leaving` fait disparaître le contenu (~180ms) avant la navigation réelle.
- Respecte `prefers-reduced-motion: reduce` (désactivé si l'utilisateur le demande).

Pour ajouter la transition à un nouveau layout : donner l'id `page-transition` à l'élément qui englobe le `@yield`/contenu de page. Aucune classe `body` particulière n'est requise, le script détecte l'élément automatiquement.

Note sidebar : dans les layouts formateur/stagiaire/observateur, la classe `transition-[margin-left] duration-300` sur le `<main>` n'est activée qu'après le premier tick Alpine (`sidebarTransitionsReady`), pour éviter un saut visuel au chargement le temps qu'Alpine restaure l'état replié/déplié depuis `localStorage`.

Note panneaux latéraux droits : les inspecteurs et tiroirs de contexte formateur qui entrent depuis la droite utilisent une transition `transform` lente et symétrique à l'ouverture comme à la fermeture (`duration-1000`). Éviter de mélanger cette animation avec une transition permanente de largeur sur le même élément, sinon le déplacement peut devenir instantané.

---

## Interactivité Alpine.js

Alpine.js est utilisé pour :
- Les menus déroulants et toggles
- Le polling AJAX des tableaux de bord en temps réel (`setInterval`)
- Les composants modaux
- L'affichage conditionnel dans les formulaires complexes
- Le repli desktop et le tiroir mobile de la navigation administrateur

Les modales partagées (`x-modal` / `x-confirm-modal`) sont téléportées dans le `<body>` et centrées dans le viewport. Elles ne doivent pas dépendre de la position de scroll ou d'un conteneur parent.

Pattern de polling AJAX type :

```html
<div x-data="{ results: [] }"
     x-init="setInterval(() => fetch('/api/results').then(r => r.json()).then(d => results = d), 2000)">
  <!-- affichage des résultats -->
</div>
```

---

## Accessibilité

### Ce qui est en place

- Attributs `aria-label` et `aria-current` dans les sidebars de navigation
- Lien « Aller au contenu principal » visible au focus dans le layout administrateur
- Contours de focus explicites : orange sur les actions du shell admin, bleu de marque sur les champs de formulaire
- Fermeture du tiroir mobile administrateur avec Échap, l'overlay ou un bouton nommé
- Focus déplacé dans le tiroir mobile à son ouverture, contenu parcouru en boucle avec Tab et restitution du focus au déclencheur à la fermeture
- Messages flash globaux refermables : erreurs et avertissements annoncés avec `role="alert"`, succès et informations avec `role="status"`
- Captions masquées visuellement et cellules d'en-tête `scope="col"` dans les nouvelles tables admin
- Badges de rôle et de statut accompagnés d'un texte, sans dépendre uniquement de la couleur
- Modales de confirmation déclarées avec `role="dialog"` et `aria-modal`, reliées à leur titre et leur description, avec focus initial, piège clavier et restitution du focus
- `image_alt` et `audio_transcript` prévus dans les questions quiz
- Polices lisibles (Varela Round arrondie, Arial en fallback)
- Lien « Aller au contenu principal » visible au focus dans les layouts public, administrateur, formateur, stagiaire et observateur
- Lien « Accessibilité » réservé au pied de page du site public, sans ajout dans les tableaux de bord applicatifs
- Information bêta non bloquante, distincte des dialogues et du gestionnaire de cookies
- Dialogue FALC fondé sur le composant modal partagé avec focus initial, piège clavier, Échap et restitution du focus

### Points d'amélioration identifiés

- Pas d'audit de contraste WCAG documenté pour `bleuone` (#004461) et `orangeone` (#E94D2A)
- Les graphiques Chart.js n'ont pas d'alternatives textuelles
- Les grandes icônes décoratives doivent avoir `aria-hidden="true"`
- Le SCORM en iframe peut poser des problèmes de navigation clavier et lecteur d'écran
- Les contenus SCORM importés peuvent avoir une accessibilité très variable — la plateforme n'impose pas de contrôle à l'import
- L'audit complet RGAA 4.1.2 et la mesure du taux de conformité restent à réaliser

La démarche et son plan de publication sont détaillés dans [Accessibilité et démarche RGAA](18-accessibilite-rgaa.md).

### Vérifications recommandées

```
- Contraste bleuone (#004461) sur blanc → ratio WCAG AA : à vérifier (≥ 4.5:1 requis)
- Contraste orangeone (#E94D2A) sur blanc → ratio WCAG AA : à vérifier
- Navigation clavier complète (tab, enter, échap sur modals)
- Test avec NVDA (Windows) ou VoiceOver (Mac)
```

---

## Icônes

Le projet utilise des icônes via la bibliothèque incluse dans le template de base. Les icônes décoratives doivent avoir `aria-hidden="true"` ; les icônes informatives doivent avoir un `aria-label` ou être accompagnées d'un texte visible.

---

[Retour au wiki](README.md)
