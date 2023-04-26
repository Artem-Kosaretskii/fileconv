<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

class FileController extends Controller
{

    public function store(Request $request){

        $request->validate([
            'file' => 'required|max:20480',
            'name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'email' => 'required|email|max:50'
        ]);
        $params = $request->query();
        $input_file = new File;
        if($request->file()) {
            $original_name = $request->file->getClientOriginalName();
            $file_name = time() . '_' . $original_name;
            $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');
            $input_file->user_id = (int)$params['user_id'] ?? 1;
            $input_file->name = $file_name;
            $input_file->path = '/storage/app/public/' . $file_path;
            $input_file->save();
            $params['file_id'] = $input_file->id;
            $this->convert($file_name, $params);
            return response()->json(['success'=>'File ' . $original_name . ' uploaded successfully']);
        } else {
            return response()->json(['error'=>'File is not uploaded']);
        }
    }

    private function convert($file_name, $params)
    {
        Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
        Settings::setPdfRendererName('DomPDF');
        $converted = preg_replace('/\.docx/','',$file_name );
        $template = new TemplateProcessor(storage_path('/app/public/uploads/'.$file_name));
        $template->setValue('name', $params['name'] ?? 'Unknown');
        $template->setValue('last_name', $params['last_name'] ?? 'Unknown');
        $template->setValue('email', $params['email'] ?? 'Unknown');
        $temp_doc = (storage_path('/app/public/uploads/'.$converted.'_temp.docx'));
        $template->saveAs($temp_doc);
        $content = IOFactory::load($temp_doc);
        $writer = IOFactory::createWriter($content,'PDF');
        $writer->save(storage_path('/app/public/converted/' .$converted. '.pdf'));
        $file = File::find($params['file_id']);
        $file->update(['converted' => '/storage/app/public/converted' .$converted. '.pdf']);
        if (file_exists($temp_doc))  unlink($temp_doc);
    }
}