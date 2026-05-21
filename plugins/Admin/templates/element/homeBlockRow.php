<?php
declare(strict_types=1);

use App\Model\Entity\Block;

$rowKey = $rowKey ?? ($isTemplate ? $templateKey : 'block-' . (string)($block['id'] ?? ''));
$blockEntity = $block instanceof Block ? $block : new Block(is_array($block) ? $block : []);
$firstError = function (string $field) use ($blockEntity): string|false {
    $errors = (array)$blockEntity->getError($field);
    if ($errors === []) {
        return false;
    }

    return (string)reset($errors);
};
$imageSrc = '';
if (!empty($block['tmp_image'])) {
    $imageSrc = str_replace('\\', '/', (string) $block['tmp_image']);
} elseif (!empty($block['image']) && !empty($block['id'])) {
    $imageSrc = $this->Html->getBlockImageSrc($blockEntity);
}
?>
<div class="home-block-row<?php echo $isTemplate ? ' template hide' : ''; ?>" data-object-id="<?php echo h($rowKey); ?>">
    <?php echo $this->Form->hidden('Blocks.' . $rowKey . '.id', ['value' => $block['id'] ?? '']); ?>

    <div class="home-block-row-fields">
        <div class="input home-block-image-input">
            <div class="home-block-image-wrapper">
                <?php
                echo $this->Html->link(
                    $imageSrc != '' ? $this->Html->image($imageSrc) : '<i class="fas fa-plus-square"></i> ' . __('Upload_image'),
                    'javascript:void(0);',
                    [
                        'class' => 'btn btn-outline-light add-image-button block-image-upload-button ' . ($imageSrc != '' ? 'uploaded' : ''),
                        'title' => __('Upload_new_image_or_change_it'),
                        'data-object-id' => $rowKey,
                        'escape' => false,
                    ]
                );
                ?>
            </div>
            <?php
            echo '<div class="home-block-image-position-wrapper' . ($imageSrc == '' ? ' hide' : '') . '">';
            echo $this->Form->control('Blocks.' . $rowKey . '.image_position', [
                'type' => 'select',
                'class' => 'selectpicker-disabled',
                'label' => false,
                'options' => [
                    Block::IMAGE_POSITION_LEFT => __('Left aligned'),
                    Block::IMAGE_POSITION_RIGHT => __('Right aligned'),
                ],
                'value' => !empty($block['image_position']) ? (int)$block['image_position'] : Block::IMAGE_POSITION_LEFT,
            ]);
            echo '</div>';
            if (($imagePositionError = $firstError('image_position')) !== false) {
                echo '<div class="error-message block-error-message">' . h($imagePositionError) . '</div>';
            }
            ?>
            <?php echo $this->Form->hidden('Blocks.' . $rowKey . '.tmp_image'); ?>
            <?php $this->Form->unlockField('Blocks.' . $rowKey . '.tmp_image'); ?>
            <?php if ($imageSrc != '') { ?>
                <?php echo $this->Form->control('Blocks.' . $rowKey . '.delete_image', [
                    'type' => 'checkbox',
                    'label' => __('Delete_image?'),
                ]); ?>
            <?php } ?>
        </div>

        <div class="home-block-text-inputs">
            <?php
            echo $this->Form->control('Blocks.' . $rowKey . '.heading', [
                'class' => 'home-block-heading-input',
                'label' => false,
                'placeholder' => __('Heading'),
                'value' => $block['heading'] ?? '',
            ]);

            echo $this->Form->control('Blocks.' . $rowKey . '.content', [
                'type' => 'textarea',
                'label' => false,
                'value' => $block['content'] ?? '',
                'id' => 'home-block-content-' . $rowKey,
            ]);

            echo '<div class="home-block-meta-row">';
                echo $this->Form->control('Blocks.' . $rowKey . '.position', [
                    'label' => __('Position'),
                    'class' => 'short',
                    'type' => 'text',
                    'value' => $block['position'] ?? 0,
                ]);
                echo $this->Form->control('Blocks.' . $rowKey . '.active', [
                    'label' => __('Active'),
                    'type' => 'checkbox',
                    'checked' => isset($block['active']) ? (bool)$block['active'] : true,
                ]);
            echo '</div>';
            if (($positionError = $firstError('position')) !== false) {
                echo '<div class="error-message block-error-message">' . h($positionError) . '</div>';
            }
            ?>
        </div>
    </div>

    <div class="home-block-row-actions">
        <button type="button" class="btn btn-danger remove-home-block-button"><i class="fa-fw fas fa-trash-alt"></i> <?php echo __('Delete'); ?></button>
        <a href="javascript:void(0);" class="btn btn-success submit"><i class="fa-fw fas fa-check"></i> <?php echo __('Save'); ?></a>
    </div>

</div>
