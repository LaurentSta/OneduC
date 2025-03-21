<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    use HasFactory;

    protected $fillable = ['nom']; // ✅ Permet l'ajout sécurisé du champ 'nom'
}
