<!-- events filters template -->
<div class="col-sm-10 flex-container">
  
<?-- selecteur caméras -->
  
<div class="custom-select">
  <button class="select-button" id="cameraSelectButton">Caméras</button>
  <div class="select-dropdown" id="cameraSelectDropdown">
    <?php foreach ($cameras as $camera): ?>
      <label class="thin-label">
        <input type="checkbox" value="<?= $camera ?>" class="cameraFilter" checked <?= in_array($camera, $selectedCameras) ? 'checked' : '' ?>> 
        <?= ucfirst($camera) ?>
</label>
    <?php endforeach; ?>
  </div>
</div>

<?-- selecteur label -->
<div class="custom-select">
  <button class="select-button" id="labelSelectButton">Labels</button>
  <div class="select-dropdown" id="labelSelectDropdown">
    <?php foreach ($labels as $label): ?>
      <label class="thin-label">
        <input type="checkbox" value="<?= $label ?>" class="labelFilter" checked <?= in_array($label, $selectedLabels) ? 'checked' : '' ?>> 
        <?= ucfirst($label) ?>
</label>
    <?php endforeach; ?>
  </div>
</div>

   
<?-- selecteur timeFilter avec radio buttons -->
<div class="custom-select">
  <button class="select-button" id="timeFilterButton">Période</button>
  <div class="select-dropdown" id="timeFilterDropdown">
    <?php foreach ($timeFilters as $value => $label): ?>
      <label class="thin-label">
        <input type="radio" name="timeFilter" value="<?= $value ?>" <?= $value === $selectedTimeFilter ? 'checked' : '' ?>>
        <?= ucfirst($label) ?>
</label>
    <?php endforeach; ?>
  </div>
</div>
      
  <div class="col-sm-4 datetime-container">
    <label for="startDate">
      <i class="fa fa-calendar"></i> Début 
      <input type="datetime-local" id="startDate" class="datetime-input">
    </label>
    <label for="endDate">
      <i class="fa fa-calendar"></i> Fin 
      <input type="datetime-local" id="endDate" class="datetime-input">
    </label>
  </div>
</div>


<script>
// Attendre que le DOM soit complètement chargé avant d'exécuter le code
document.addEventListener('DOMContentLoaded', function() {
  
  // Gestion du sélecteur de caméras
  const cameraButton = document.getElementById('cameraSelectButton'); // Récupérer le bouton pour ouvrir le dropdown des caméras
  const cameraDropdown = document.getElementById('cameraSelectDropdown'); // Récupérer le menu déroulant des caméras

  // Lorsque l'utilisateur clique sur le bouton des caméras
  cameraButton.addEventListener('click', function() {
    // Alterner entre afficher et masquer le menu dropdown
    cameraDropdown.style.display = cameraDropdown.style.display === 'block' ? 'none' : 'block';
  });

  // Gestion du sélecteur de labels
  const labelButton = document.getElementById('labelSelectButton'); // Récupérer le bouton pour ouvrir le dropdown des labels
  const labelDropdown = document.getElementById('labelSelectDropdown'); // Récupérer le menu déroulant des labels

  // Lorsque l'utilisateur clique sur le bouton des labels
  labelButton.addEventListener('click', function() {
    // Alterner entre afficher et masquer le menu dropdown
    labelDropdown.style.display = labelDropdown.style.display === 'block' ? 'none' : 'block';
  });

  // Fermer les dropdowns si on clique en dehors, sauf si c'est sur une checkbox
  window.addEventListener('click', function(e) {
    // Si l'élément cliqué n'est pas une checkbox et pas un bouton de type 'select-button'
    if (!e.target.matches('.select-button') && !e.target.matches('input[type="checkbox"]')) {
      cameraDropdown.style.display = 'none';
      labelDropdown.style.display = 'none';
    }
  });
});

// Deuxième script pour gérer le dropdown du timeFilter (filtre de temps)
document.addEventListener('DOMContentLoaded', function() {
  
  // Gestion du sélecteur de période (timeFilter)
  const timeFilterButton = document.getElementById('timeFilterButton'); // Récupérer le bouton pour ouvrir le dropdown du timeFilter
  const timeFilterDropdown = document.getElementById('timeFilterDropdown'); // Récupérer le menu déroulant du timeFilter
  
  // Lorsque l'utilisateur clique sur le bouton de filtre de temps
  timeFilterButton.addEventListener('click', function() {
    // Alterner entre afficher et masquer le menu dropdown
    timeFilterDropdown.style.display = timeFilterDropdown.style.display === 'block' ? 'none' : 'block';
  });

  // Fermer le dropdown si l'utilisateur clique en dehors
  window.addEventListener('click', function(e) {
    // Vérifier si l'élément cliqué n'est pas un bouton de type '.select-button'
    if (!e.target.matches('.select-button')) {
      // Si l'utilisateur clique en dehors du bouton, cacher le dropdown du timeFilter
      timeFilterDropdown.style.display = 'none';
    }
  });
});


</script>
<style>
      /* Styles pour les selecteurs */
.custom-select {
  position: relative;
  display: inline-block;
  margin-bottom: 10px;
  min-width: 125px;
}

.select-button {
  border: 1px solid #ccc;
  padding: 5px;
  cursor: pointer;
  background-color: rgb(var(--defaultBkg-color));
  width: 100%;
}

.select-dropdown {
  display: none;
  position: absolute;
  background-color: rgb(var(--defaultBkg-color));
  min-width: 100%;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 100;
}

.select-dropdown label {
  display: block;
  padding: 8px;
}

.select-dropdown label:hover {
  background-color: rgb(var(--defaultBkg-color));
}

/* Styles pour les champs datetime */
.datetime-container {
  display: flex;
  gap: 10px;
}

.datetime-input {
  padding: 5px;
  width: 140px; /* ajustez la taille selon vos besoins */
}

.datetime-container label {
  display: flex;
  align-items: center;
  gap: 5px;
  font-weight: 300; /* Le texte sera plus fin */
  font-size: 10px;  /* Taille de police légèrement plus petite */
  color: rgb(var(--defaultText-color));     /* Couleur plus douce */
}

.fa-calendar {
  color: #007bff;
  font-size: 1.2em;
}

.custom-select .select-dropdown label.thin-label {
  font-weight: 300; /* Le texte sera plus fin */
  font-size: 14px;  /* Taille de police légèrement plus petite */
  color: rgb(var(--defaultText-color));     /* Couleur plus douce */
  margin-bottom: 8px;
  display: block;
}

.custom-select .select-dropdown label.thin-label input {
  margin-right: 8px;
}
</style>