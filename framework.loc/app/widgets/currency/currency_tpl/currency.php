<option value="" class="label"><?= self::$currency['code'] ?></option>
<?php foreach (self::$currencies as $k => $v): ?>
    <?php if ($k != self::$currency['code']): ?>
        <option value="<?= $k; ?>"><?= $k; ?></option>
    <?php endif; ?>
<?php endforeach;?>
