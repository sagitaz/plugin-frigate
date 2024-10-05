<!-- events filters template -->
<div class="col-sm-10 flex-container">
  <a class="btn btn-info button-xs" id="selectAllCameras" style="margin-right:10px"><i class="fas fa-check"></i>
    {{Tout}}</a>
  <a class="btn btn-info button-xs" id="deselectAllCameras" style="margin-right:20px"><i class="fas fa-times"></i>
    {{Aucun}}</a>
  <div class="checkbox-container">
    <?php foreach ($cameras as $camera): ?>
      <?php $isChecked = empty($selectedCameras) || in_array($camera, $selectedCameras); ?>
      <label>
        <input type="checkbox" class="eqLogicAttr cameraFilter" value="<?= $camera ?>" <?= $isChecked ? 'checked' : '' ?>>
        <span class="custom-checkbox"></span> <?= ucfirst($camera) ?>
      </label>
    <?php endforeach; ?>
  </div>
</div>

<div class="col-sm-10 flex-container">
  <a class="btn btn-info button-xs" id="selectAllLabels" style="margin-right:10px"><i class="fas fa-check"></i>
    {{Tout}}</a>
  <a class="btn btn-info button-xs" id="deselectAllLabels" style="margin-right:20px"><i class="fas fa-times"></i>
    {{Aucun}}</a>
  <div class="checkbox-container">
    <?php foreach ($labels as $label): ?>
      <?php $isChecked = empty($selectedLabels) || in_array($label, $selectedLabels); ?>
      <label>
        <input type="checkbox" class="eqLogicAttr labelFilter" value="<?= $label ?>" <?= $isChecked ? 'checked' : '' ?>>
        <span class="custom-checkbox"></span> <?= ucfirst($label) ?>
      </label>
    <?php endforeach; ?>
  </div>
</div>

<div class="col-sm-12" style="margin-bottom:10px">
  <div class="col-sm-4 datetime-container">
    <label>Entre <input type="date" id="startDate"></label>
    <label>et <input type="date" id="endDate"></label>
    <label>Ou de </label>
  </div>

  <div class="col-sm-8 radio-container">
    <?php foreach ($timeFilters as $value => $label): ?>
      <?php $isChecked = $value === $selectedTimeFilter; ?>
      <label>
        <input type="radio" name="timeFilter" value="<?= $value ?>" <?= $isChecked ? 'checked' : '' ?>>
        <span class="custom-radio"></span> <?= $label ?>
      </label>
    <?php endforeach; ?>
  </div>
</div>