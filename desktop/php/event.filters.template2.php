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
      <button id="clearDates" class="select-button" title="{{Effacer les dates sélectionnées}}">
    <i class="fas fa-redo"></i>
  </button>
  </div>
</div>

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