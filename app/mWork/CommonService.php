<?php

namespace App\mWork;

use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\mWork\Core;
use PDF;

class CommonService
{

    public function indianAllStatesList()
    {
        $indian_all_states = [
            ['id' => 1, 'name' => 'Andhra Pradesh'],
            ['id' => 2, 'name' => 'Arunachal Pradesh'],
            ['id' => 3, 'name' => 'Assam'],
            ['id' => 4, 'name' => 'Bihar'],
            ['id' => 5, 'name' => 'Chhattisgarh'],
            ['id' => 6, 'name' => 'Goa'],
            ['id' => 7, 'name' => 'Gujarat'],
            ['id' => 8, 'name' => 'Haryana'],
            ['id' => 9, 'name' => 'Himachal Pradesh'],
            ['id' => 10, 'name' => 'Jammu & Kashmir'],
            ['id' => 11, 'name' => 'Jharkhand'],
            ['id' => 12, 'name' => 'Karnataka'],
            ['id' => 13, 'name' => 'Kerala'],
            ['id' => 14, 'name' => 'Madhya Pradesh'],
            ['id' => 15, 'name' => 'Maharashtra'],
            ['id' => 16, 'name' => 'Manipur'],
            ['id' => 17, 'name' => 'Meghalaya'],
            ['id' => 18, 'name' => 'Mizoram'],
            ['id' => 19, 'name' => 'Nagaland'],
            ['id' => 20, 'name' => 'Odisha'],
            ['id' => 21, 'name' => 'Punjab'],
            ['id' => 22, 'name' => 'Rajasthan'],
            ['id' => 23, 'name' => 'Sikkim'],
            ['id' => 24, 'name' => 'Tamil Nadu'],
            ['id' => 25, 'name' => 'Tripura'],
            ['id' => 26, 'name' => 'Uttarakhand'],
            ['id' => 27, 'name' => 'Uttar Pradesh'],
            ['id' => 28, 'name' => 'West Bengal'],
            ['id' => 29, 'name' => 'Andaman & Nicobar'],
            ['id' => 30, 'name' => 'Chandigarh'],
            ['id' => 31, 'name' => 'Dadra and Nagar Haveli'],
            ['id' => 32, 'name' => 'Daman & Diu'],
            ['id' => 33, 'name' => 'Delhi'],
            ['id' => 34, 'name' => 'Lakshadweep'],
            ['id' => 35, 'name' => 'Puducherry']
        ];
        return $indian_all_states;
    }
    public function permissionList()
    {
        $path="constants.permistionsidebar";
        $query=Config::get($path);
        if(!is_null($query))
        {
             $ResponseData = array("code" => 100,'message'=>"permission List.",'status'=>'success','data'=>$query);
          return  $this->responseFun($ResponseData);

            
        }
        else
        {
            throw new \Exception("permission not found");
        }
       
    }

    public function StateNameById($id)
    {
        $states = $this->indianAllStatesList();
        foreach ($states as $state) {
            if ($state['id'] == $id) {
                return $state;
            }
        }
    }

    public function converBase64ToFileObject($encodedString)
    {
        $fileData = base64_decode($encodedString);
        $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
        file_put_contents($tmpFilePath, $fileData);
        $tmpFile = new File($tmpFilePath);

        $file = new UploadedFile(
            $tmpFile->getPathname(),
            $tmpFile->getFilename(),
            $tmpFile->getMimeType(),
            0,
            true
        );
        return $file;
    }

    public function uploadAttachment($file, $extension, $key, $id, $host)
    {
        $filename = $key . '-' . time() . '.' . $extension;
        $path = $file->storeAs('public/users/' . $id, $filename);
        $url = $host . Core::getLabel('PROJECT_FOLDER_PREFIX') . '/public' . storage::url($path);
        return $url;
    }

    public function generatePDFAttachment($data, $host, $view, $fileName)
    {
        try {
            $pdf = PDF::loadView($view, $data);
            $pdfName = 'public/Invoice/' . $fileName;
            Storage::put($pdfName, $pdf->output());
            $url = $host . Core::getLabel('PROJECT_FOLDER_PREFIX') . '/public' . Storage::url($pdfName);
            return $url;
        } catch (\Exception $e) {
            throw new \Exception("Error While Generating PDF Attachment");
        }
    }
}
