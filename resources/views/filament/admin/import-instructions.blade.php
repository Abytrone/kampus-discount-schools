<div class="space-y-4">
    <div class="rounded-lg bg-primary-50 dark:bg-primary-900/20 p-4 border border-primary-200 dark:border-primary-800">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-primary-900 dark:text-primary-100 mb-2">How to Import Students</h3>
                <ol class="list-decimal list-inside space-y-1.5 text-sm text-primary-800 dark:text-primary-200">
                    <li>Click the <strong>"Download Template"</strong> button below to get the CSV template</li>
                    <li>Open the template in Excel, Google Sheets, or any spreadsheet application</li>
                    <li>Fill in the student data following the column headers</li>
                    <li>Save the file as a CSV format</li>
                    <li>Upload the completed CSV file using the file picker above</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 p-4 border border-amber-200 dark:border-amber-800">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-100 mb-2">Important Field Requirements</h3>
                <div class="space-y-2 text-sm text-amber-800 dark:text-amber-200">
                    <div>
                        <strong class="text-amber-900 dark:text-amber-100">Required Fields:</strong>
                        <ul class="list-disc list-inside ml-2 mt-1 space-y-0.5">
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">school_id</code> - Use school code or name</li>
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">first_name</code> - Student's first name</li>
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">last_name</code> - Student's last name</li>
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">gender</code> - Must be "M" or "F"</li>
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">date_of_birth</code> - Format: YYYY-MM-DD (e.g., 2008-05-15)</li>
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">form</code> - Must be 1, 2, or 3</li>
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">year_of_admission</code> - 4-digit year (e.g., 2024)</li>
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">admission_status</code> - Active, Transfer, Completed, or Dropped</li>
                        </ul>
                    </div>
                    <div>
                        <strong class="text-amber-900 dark:text-amber-100">Optional Fields:</strong>
                        <ul class="list-disc list-inside ml-2 mt-1 space-y-0.5">
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">kd_shs_id</code> - Auto-generated if empty</li>
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">other_names</code>, <code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">programme</code>, <code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">house</code>, <code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">index_number</code></li>
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">ghana_card_number</code> - Format: GHA-12345678-A</li>
                            <li><code class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded text-xs">completion_year</code> - Auto-calculated if empty (admission year + 3)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-success-50 dark:bg-success-900/20 p-4 border border-success-200 dark:border-success-800">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-success-600 dark:text-success-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-success-900 dark:text-success-100 mb-2">Tips for Success</h3>
                <ul class="list-disc list-inside space-y-1 text-sm text-success-800 dark:text-success-200">
                    <li>Keep the header row exactly as provided in the template</li>
                    <li>Do not modify column names or add extra columns</li>
                    <li>School lookup accepts both school codes and school names (partial match supported)</li>
                    <li>Date formats are flexible - YYYY-MM-DD, YYYY/MM/DD, DD-MM-YYYY are all accepted</li>
                    <li>If a student with the same GES-SHS ID or Ghana Card Number exists, the record will be updated</li>
                    <li>Empty optional fields can be left blank</li>
                </ul>
            </div>
        </div>
    </div>
</div>

