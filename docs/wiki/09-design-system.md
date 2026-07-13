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

## Pattern de carte standard (dashboards)

Le pattern de carte utilisé dans tous les tableaux de bord :

```html
<div class="bg-white rounded-[20px] shadow-md p-6">
  <!-- contenu -->
</div>
```

`rounded-[20px]` est la valeur arbitraire Tailwind utilisée de façon cohérente sur l'ensemble des dashboards. Ne pas utiliser `rounded-3xl` (32px) ou `rounded-2xl` (16px) sur ces éléments pour maintenir la cohérence visuelle.

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

Les anciennes tailles `text-titre` et `text-sous-titre` restent disponibles pour les pages publiques ou les héros, mais ne doivent plus être utilisées pour les en-têtes courants des dashboards stagiaire et formateur.

---

## Transition de page (fondu)

Toutes les pages (front public, dashboards formateur/stagiaire/observateur/admin) partagent le même mécanisme de fondu à la navigation, centralisé dans `resources/js/app.js` (fonction `initPageTransitions`) et `resources/css/app.css` (règles `#page-transition`).

Fonctionnement :
- Chaque layout place le contenu de page dans `<main id="page-transition">`.
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
- `image_alt` et `audio_transcript` prévus dans les questions quiz
- Polices lisibles (Varela Round arrondie, Arial en fallback)

### Points d'amélioration identifiés

- Pas d'audit de contraste WCAG documenté pour `bleuone` (#004461) et `orangeone` (#E94D2A)
- Les graphiques Chart.js n'ont pas d'alternatives textuelles
- Les grandes icônes décoratives doivent avoir `aria-hidden="true"`
- Le SCORM en iframe peut poser des problèmes de navigation clavier et lecteur d'écran
- Les contenus SCORM importés peuvent avoir une accessibilité très variable — la plateforme n'impose pas de contrôle à l'import

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
