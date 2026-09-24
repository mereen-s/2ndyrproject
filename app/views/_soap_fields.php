<?php $f = $f ?? null; ?>
<h3>History</h3>
<div class="row">
  <div style="flex:3"><label>Presenting history / how the patient feels</label>
    <input name="history_note" value="<?= e($f['history_note'] ?? '') ?>"></div>
  <div><label>Pain 0&ndash;10</label>
    <input data-type="numeric" name="pain" type="number" min="0" max="10" value="<?= e($f['pain'] ?? '') ?>"></div>
</div>
<h3>Examination</h3>
<div class="row">
  <div><label>Temp &deg;C</label><input data-type="numeric" name="temp" value="<?= e($f['temp'] ?? '') ?>"></div>
  <div><label>BP</label><input name="bp" value="<?= e($f['bp'] ?? '') ?>"></div>
  <div><label>Pulse</label><input data-type="numeric" name="pulse" type="number" value="<?= e($f['pulse'] ?? '') ?>"></div>
  <div style="flex:2"><label>Examination findings</label><input name="exam_note" value="<?= e($f['exam_note'] ?? '') ?>"></div>
</div>
<h3>Investigations</h3>
<div class="row">
  <div style="flex:1"><label>Investigations summary (ordered / results so far)</label>
    <input name="investigations_note" value="<?= e($f['investigations_note'] ?? '') ?>"></div>
</div>
<p class="note">Order new tests from the patient's record page.</p>
<h3>Management</h3>
<div class="row">
  <div><label>Progress</label>
    <select name="progress">
      <option value="">&mdash;</option>
      <?php foreach (['Improving','Stable','Deteriorating'] as $o): ?>
        <option <?= (($f['progress'] ?? '')===$o)?'selected':'' ?>><?= $o ?></option>
      <?php endforeach; ?>
    </select></div>
  <div style="flex:2"><label>Diagnosis / impression</label>
    <input name="diagnosis" value="<?= e($f['diagnosis'] ?? '') ?>"></div>
</div>
<div class="row">
  <div><label>Medication</label>
    <select name="med_action">
      <option value="">&mdash;</option>
      <?php foreach (['Continue','Change','Stop'] as $o): ?>
        <option <?= (($f['med_action'] ?? '')===$o)?'selected':'' ?>><?= $o ?></option>
      <?php endforeach; ?>
    </select></div>
  <div style="flex:3"><label>Management plan</label>
    <input name="plan_note" value="<?= e($f['plan_note'] ?? '') ?>"></div>
</div>
