<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    // ✅ Laravel cherche par défaut "sub_categories", donc on force le nom réel ici
    protected $table = 'subcategories';

    // ✅ Tous les champs autorisés pour le mass assignment
    protected $fillable = [
        'category_id',
        'subcategory_name',
        'subcategory_slug',
        'subcategory_description', // ✅ rajouté
        'subcategory_image'        // ✅ rajouté
    ];

    // ✅ Relation avec la catégorie parente
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    // app/Models/SubCategory.php

    public function modules()
    {
        return $this->hasMany(\App\Models\Module::class, 'subcategory_id');
    }



}
