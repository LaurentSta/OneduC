<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\Module;

class CategoryController extends Controller
{
    public function AllCategory()
    {
        $categories = Category::withCount('subcategories')->latest()->get();
        return view('admin.backend.categorie.categorie', compact('categories'));
    }
    public function AddCategory()
    {
        return view('admin.backend.categorie.ajout_categorie');
    }
    public function StoreCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'category_image' => 'nullable|file|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml|max:2048',
        ]);
        // Stocker l'image avec un nom unique
        $imagePath = $request->file('category_image')->store('category_images', 'public');
        // Enregistrement en base de données
        Category::create([
            'category_name' => $request->category_name,
            'category_description' => $request->category_description,
            'category_slug' => Str::slug($request->category_name),
            'category_image' => $imagePath
        ]);
        return redirect()->route('admin.categories.all')->with('success', 'Catégorie ajoutée avec succès 🎉');
    }

    public function EditCategory($id)
    {
        $category = Category::findOrFail($id);

        return view('admin.backend.categorie.edit_categorie', compact('category'));
    }

    public function UpdateCategory(Request $request)
    {
        // Validation des données
        $request->validate([
            'id' => 'required|exists:categories,id',
            'category_name' => 'required|string|max:255',
            'category_description' => 'nullable|string',
            'category_image' => 'nullable|file|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml|max:2048',

        ]);

        // Récupération de la catégorie à mettre à jour
        $category = Category::findOrFail($request->id);

        // Préparer les données à mettre à jour
        $data = [
            'category_name' => $request->category_name,
            'category_slug' => Str::slug($request->category_name),
            'category_description' => $request->category_description,
        ];

        // Gestion de l'image si une nouvelle est uploadée
        if ($request->hasFile('category_image')) {

            // Suppression de l'ancienne image si elle existe
            if ($category->category_image && Storage::disk('public')->exists($category->category_image)) {
                Storage::disk('public')->delete($category->category_image);
            }

            // Stocker la nouvelle image
            $data['category_image'] = $request->file('category_image')->store('category_images', 'public');
        }

        // Mise à jour en base de données
        $category->update($data);

        // Retour avec succès
        return redirect()->route('admin.categories.all')->with('success', 'Catégorie mise à jour avec succès 🎉');
    }

        // Page front  categories
    public function FrontCategories()
    {
        $categories = Category::orderBy('created_at', 'asc')->get();
        return view('frontend.contenu.categories', compact('categories'));
    }
    // Affiche les sous-catégories d'une catégorie spécifique
    public function showSubCategories($id)
    {
        $category = Category::findOrFail($id);
        $subcategories = SubCategory::where('category_id', $id)
                                    ->with('modules') // 👈 Important ici !
                                    ->latest()
                                    ->get();

        return view('frontend.contenu.subcategories', compact('subcategories', 'category'));
    }

    // Affiche les modules liés à une catégorie spécifique
    public function showCategoryModules($id)
    {
        $category = Category::findOrFail($id);
        $modules = Module::where('category_id', $id)->latest()->get();

        return view('frontend.contenu.category_modules', compact('modules', 'category'));
    }
    public function DeleteCategory($id)
    {
        $category = Category::findOrFail($id);

        // Supprimer l'image de stockage si elle existe
        if (Storage::disk('public')->exists($category->category_image)) {
            Storage::disk('public')->delete($category->category_image);
        }

        $category->delete();

        return redirect()->back()->with('success', 'Catégorie supprimée avec succès');
    }
     // ✅ Liste toutes les sous-catégories
     public function AllSubCategory(Request $request)
    {
        $query = SubCategory::with('category')->latest();

        // Optionnel : filtrer par catégorie parente
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $subcategories = $query->get();

        return view('admin.backend.souscategorie.sous-categorie', [
            'subcategories' => $subcategories,
        ]);
    }




    // ✅ Affiche le formulaire d'ajout
    public function AddSubCategory()
    {
        $categories = Category::latest()->get();

        // ⚠️ doit pointer vers la vue de création (et non vers la vue de liste !)
        return view('admin.backend.souscategorie.ajout_sous-categorie', compact('categories'));
    }


    public function StoreSubCategory(Request $request)
        {
            $request->validate([
                'category_id' => 'required|exists:categories,id',
                'subcategory_name' => 'required|string|max:255|unique:subcategories,subcategory_name',
                'subcategory_description' => 'nullable|string',
                'subcategory_image' => 'nullable|file|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml|max:2048',
            ]);

            $imagePath = null;

            if ($request->hasFile('subcategory_image')) {
                $file = $request->file('subcategory_image');

                // Donne un nom unique et propre au fichier
                $fileName = time() . '_' . Str::slug($request->subcategory_name) . '.' . $file->getClientOriginalExtension();

                // Stocke le fichier dans le dossier public/storage/subcategory_images
                $imagePath = $file->storeAs('subcategory_images', $fileName, 'public');
            }

            SubCategory::create([
                'category_id' => $request->category_id,
                'subcategory_name' => $request->subcategory_name,
                'subcategory_slug' => Str::slug($request->subcategory_name),
                'subcategory_description' => $request->subcategory_description,
                'subcategory_image' => $imagePath, // stocké en BDD sous "subcategory_images/monfichier.jpg"
            ]);

            return redirect()->route('admin.subcategories.all')->with('success', 'Sous-catégorie ajoutée avec succès !');
        }



    // ✅ Affiche le formulaire d'édition
    public function EditSubCategory($id)
    {
        $categories = Category::latest()->get();
        $subcategory = SubCategory::findOrFail($id);

        return view('admin.backend.souscategorie.edit_sous-categorie', compact('categories', 'subcategory'));
    }

    // ✅ Met à jour la sous-catégorie
    public function UpdateSubCategory(Request $request)
    {
        // Validation
        $request->validate([
            'id' => 'required|exists:subcategories,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_name' => 'required|string|max:255|unique:subcategories,subcategory_name,' . $request->id,
            'subcategory_description' => 'nullable|string',
            'subcategory_image' => 'nullable|file|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml|max:2048',
        ]);

        $subcategory = SubCategory::findOrFail($request->id);

        // Préparation des données
        $data = [
            'category_id' => $request->category_id,
            'subcategory_name' => $request->subcategory_name,
            'subcategory_slug' => strtolower(str_replace(' ', '-', $request->subcategory_name)),
            'subcategory_description' => $request->subcategory_description ?? null,
        ];

        // Si une nouvelle image est envoyée
        if ($request->hasFile('subcategory_image')) {
            // Supprime l'ancienne image si elle existe
            if ($subcategory->subcategory_image && Storage::disk('public')->exists($subcategory->subcategory_image)) {
                Storage::disk('public')->delete($subcategory->subcategory_image);
            }

            // Stocke la nouvelle image
            $data['subcategory_image'] = $request->file('subcategory_image')->store('subcategory_images', 'public');
        }

        $subcategory->update($data);

        return redirect()->route('admin.subcategories.all')->with('success', 'Sous-catégorie mise à jour avec succès !');
    }

    // ✅ Supprime la sous-catégorie
    public function DeleteSubCategory($id)
    {
        $subcategory = SubCategory::findOrFail($id);

        // Supprime l'image associée si elle existe
        if ($subcategory->subcategory_image && Storage::disk('public')->exists($subcategory->subcategory_image)) {
            Storage::disk('public')->delete($subcategory->subcategory_image);
        }

        $subcategory->delete();

        return redirect()->back()->with('success', 'Sous-catégorie supprimée avec succès !');
    }
}
