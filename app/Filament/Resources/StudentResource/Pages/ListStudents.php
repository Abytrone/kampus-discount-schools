<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Imports\StudentImporter;
use App\Filament\Resources\StudentResource;
use App\Models\School;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    return $this->downloadTemplate();
                })
                ->tooltip('Download CSV template for bulk student import')
                ->outlined(),
            
            Actions\ImportAction::make()
                ->importer(StudentImporter::class)
                ->label('Import Students')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->successNotificationTitle('Students imported successfully')
                ->tooltip('Import students from CSV file')
                ->outlined()
                ->modalHeading('Import Students from CSV')
                ->modalDescription('Upload a CSV file containing student data. Make sure you have downloaded and filled the template correctly.')
                ->modalSubmitActionLabel('Import Students')
                ->modalWidth('4xl')
                ->modalContent(fn (): View => view('filament.admin.import-instructions'))
                ->extraModalFooterActions([
                    Actions\Action::make('downloadTemplateFromModal')
                        ->label('Download Template')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->outlined()
                        ->action(function () {
                            return $this->downloadTemplate();
                        }),
                ]),
            
            Actions\CreateAction::make(),
        ];
    }

    protected function downloadTemplate()
    {
        // Get a sample school for the template
        $sampleSchool = School::first();
        $sampleSchoolCode = $sampleSchool ? $sampleSchool->school_code : '50102';
        
        // Clean column headers matching database structure
        $headers = [
            'kd_shs_id',
            'school_id',
            'first_name',
            'last_name',
            'other_names',
            'gender',
            'date_of_birth',
            'form',
            'programme',
            'house',
            'index_number',
            'ghana_card_number',
            'year_of_admission',
            'completion_year',
            'admission_status',
        ];
        
        // One simple sample row
        $sampleRow = [
            '', // kd_shs_id - auto-generated if empty
            $sampleSchoolCode, // school_id - use school code or name
            'Kwame', // first_name
            'Mensah', // last_name
            'Kofi', // other_names
            'M', // gender (M or F)
            '2008-05-15', // date_of_birth (YYYY-MM-DD)
            '1', // form (1, 2, or 3)
            'Science', // programme
            'Red House', // house
            'BECE2024001', // index_number
            'GHA-12345678-A', // ghana_card_number
            '2024', // year_of_admission
            '2027', // completion_year
            'Active', // admission_status (Active, Transfer, Completed, Dropped)
        ];
        
        // Build clean CSV content
        $csvContent = $this->arrayToCsv($headers) . "\n";
        $csvContent .= $this->arrayToCsv($sampleRow) . "\n";
        
        $filename = 'student_import_template.csv';
        
        return Response::streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $data);
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return rtrim($csv, "\n");
    }
}
