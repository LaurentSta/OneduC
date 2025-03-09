<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CategoryController extends Controller
{
    public function AllCategory()
    {
        $categories = Category::latest()->get();
        return view('admin.backend.category.all_category', compact('categories'));
    }

    public function AddCategory()
    {
        return view('admin.backend.category.add_category');
    }

    public function StoreCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'category_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Stocker l'image avec un nom unique
        $imagePath = $request->file('category_image')->store('category_images', 'public');

        // Enregistrement en base de données
        Category::create([
            'category_name' => $request->category_name,
            'category_slug' => Str::slug($request->category_name),
            'category_image' => $imagePath
        ]);

        return redirect()->route('all.category')->with('success', 'Catégorie ajoutée avec succès 🎉');
    }

    public function EditCategory($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.backend.category.edit_category', compact('category'));
    }

    public function UpdateCategory(Request $request)
    {
        $category = Category::findOrFail($request->id);

        $data = [
            'category_name' => $request->category_name,
            'category_slug' => Str::slug($request->category_name),
        ];

        // Vérifier si une nouvelle image est téléchargée
        if ($request->hasFile('category_image')) {
            // Supprimer l'ancienne image si elle existe
            if (Storage::disk('public')->exists($category->category_image)) {
                Storage::disk('public')->delete($category->category_image);
            }

            // Enregistrer la nouvelle image
            $data['category_image'] = $request->file('category_image')->store('category_images', 'public');
        }

        $category->update($data);

        return redirect()->route('all.category')->with('success', 'Catégorie mise à jour avec succès');
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
}
