@foreach($uniqueStudents as $attendance)
<tr class="hover:bg-surface-container-low/50 transition-colors">
    <td class="py-5 px-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary-fixed text-on-primary-fixed flex items-center justify-center font-bold text-sm">{{ strtoupper(substr($attendance->student->name, 0, 2)) }}</div>
            <span class="font-semibold text-on-surface text-base">{{ $attendance->student->name }}</span>
        </div>
    </td>
    <td class="py-5 px-6 text-on-surface-variant text-base font-medium">{{ $attendance->snumber }}</td>
    <td class="py-5 px-6 text-on-surface-variant text-base font-medium">{{ $attendance->student->section }}</td>
    <td class="py-5 px-6 text-on-surface-variant text-base font-medium">{{ $attendance->student->program ?? '-' }}</td>
    <td class="py-5 px-6">
        <span class="px-3 py-2 bg-surface-container-high rounded font-mono text-xs text-on-surface-variant">{{ $attendance->student->rfid ?? '-' }}</span>
    </td>
    <td class="py-5 px-6 text-right">
        <div class="flex items-center justify-end gap-3">
            <button type="button" class="p-1 text-on-surface-variant hover:text-primary transition-colors viewBarcodeBtn" data-student-id="{{ $attendance->snumber }}" data-student-name="{{ $attendance->student->name }}" data-student-rfid="{{ $attendance->student->rfid ?? 'N/A' }}" data-student-section="{{ $attendance->student->section }}" title="View & Download Barcode">
                <span class="material-symbols-outlined">barcode</span>
            </button>
            <button type="button" class="p-1 text-on-surface-variant hover:text-error transition-colors deleteAttendanceBtn" data-attendance-id="{{ $attendance->id }}" title="Delete Student">
                <span class="material-symbols-outlined">delete</span>
            </button>
        </div>
    </td>
</tr>
@endforeach
