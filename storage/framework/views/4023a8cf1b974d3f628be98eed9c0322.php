<?php $__currentLoopData = $uniqueStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr class="hover:bg-surface-container-low/50 transition-colors">
    <td class="py-5 px-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary-fixed text-on-primary-fixed flex items-center justify-center font-bold text-sm"><?php echo e(strtoupper(substr($attendance->student->name, 0, 2))); ?></div>
            <span class="font-semibold text-on-surface text-base"><?php echo e($attendance->student->name); ?></span>
        </div>
    </td>
    <td class="py-5 px-6 text-on-surface-variant text-base font-medium"><?php echo e($attendance->snumber); ?></td>
    <td class="py-5 px-6 text-on-surface-variant text-base font-medium"><?php echo e($attendance->student->section); ?></td>
    <td class="py-5 px-6 text-on-surface-variant text-base font-medium"><?php echo e($attendance->student->program ?? '-'); ?></td>
    <td class="py-5 px-6">
        <span class="px-3 py-2 bg-surface-container-high rounded font-mono text-xs text-on-surface-variant"><?php echo e($attendance->student->rfid ?? '-'); ?></span>
    </td>
    <td class="py-5 px-6 text-right">
        <div class="flex items-center justify-end gap-3">
            <button type="button" class="p-1 text-on-surface-variant hover:text-primary transition-colors viewBarcodeBtn" data-student-id="<?php echo e($attendance->snumber); ?>" data-student-name="<?php echo e($attendance->student->name); ?>" data-student-rfid="<?php echo e($attendance->student->rfid ?? 'N/A'); ?>" data-student-section="<?php echo e($attendance->student->section); ?>" title="View & Download Barcode">
                <span class="material-symbols-outlined">barcode</span>
            </button>
            <button type="button" class="p-1 text-on-surface-variant hover:text-error transition-colors deleteAttendanceBtn" data-attendance-id="<?php echo e($attendance->id); ?>" title="Delete Student">
                <span class="material-symbols-outlined">delete</span>
            </button>
        </div>
    </td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\AttendanceEvent\resources\views/event/partials/attendee-list-rows.blade.php ENDPATH**/ ?>