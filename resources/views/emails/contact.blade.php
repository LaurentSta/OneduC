<h2>Nouveau message de contact</h2>

<p><strong>Profil :</strong> {{ ucfirst($data['type_utilisateur']) }}</p>
<p><strong>Objet :</strong> {{ str_replace('_',' ', $data['objet']) }}</p>

<p><strong>Nom :</strong> {{ ($data['prenom'] ?? '') ? $data['prenom'].' ' : '' }}{{ $data['nom'] }}</p>
<p><strong>Email :</strong> {{ $data['email'] }}</p>
@if(!empty($data['phone'])) <p><strong>Téléphone :</strong> {{ $data['phone'] }}</p> @endif
@if(!empty($data['heure_appel'])) <p><strong>Créneau :</strong> {{ $data['heure_appel'] }}</p> @endif

<hr>
<p style="white-space:pre-line">{{ $data['message'] }}</p>
