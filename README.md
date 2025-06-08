# 🎓 Oneduc — Plateforme de formation en ligne (Laravel 11)

**Version actuelle :** v1.4  
**Statut :** Privé · En développement

---

## 📚 Description

Oneduc est une plateforme pédagogique développée sous Laravel 11, intégrant :
- une gestion de modules SCORM (iSpring),
- des rôles multiples (Admin, Formateur, Stagiaire),
- des tableaux de bord personnalisés,
- des fonctionnalités RGPD, accessibilité et retours utilisateurs.

---

## 🔧 Technologies

- PHP / Laravel 11
- Tailwind CSS
- Inertia.js (React à venir)
- SCORM (tracking, scores, progression)
- Base de données MySQL

---

## 🔒 Dépôt privé

Ce projet contient des éléments confidentiels :
- intégration SCORM avancée
- structure pédagogique propriétaire
- données utilisateurs (en local)
- design UI spécifique

---

## 🗺️ Objectifs

- Finaliser la version publique de septembre
- Ajouter la génération de fiches pédagogiques complètes
- Implémenter les évaluations finales certifiantes

---

## 📁 Structure des répertoires

| Dossier                | Description |
|------------------------|-------------|
| `app/`                | Contrôleurs, modèles, logique métier |
| `resources/views/`    | Vues Blade (Admin, Formateur, Stagiaire) |
| `public/modules/`     | Contenus SCORM |
| `routes/`             | Routes Laravel (`web.php`, `admin.php`, etc.) |
| `database/`           | Migrations et seeders |

---

## 🧪 Environnement local

- `.env` non versionné
- Utiliser XAMPP / Laravel Sail
- Commandes utiles :
```bash
php artisan migrate
php artisan serve
