<?php

namespace App\Controllers\Api;

use App\Models\MedicalAttachmentModel;
use Throwable;

class MedicalAttachmentsController extends BaseApiController
{
    public function create()
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $rules = [
            'facility_id' => 'required|is_natural_no_zero',
            'medicine_id' => 'permit_empty|is_natural_no_zero',
            'batch_id'    => 'permit_empty|is_natural_no_zero',
            'attachment'  => [
                'rules' => 'uploaded[attachment]|max_size[attachment,4096]|ext_in[attachment,jpg,jpeg,png,pdf]|mime_in[attachment,image/jpg,image/jpeg,image/png,application/pdf]',
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->validationFailed($this->validator->getErrors());
        }

        $file = $this->request->getFile('attachment');

        if ($file === null || ! $file->isValid()) {
            return $this->badRequest('Valid attachment file is required.');
        }

        $mimeType = $file->getClientMimeType();
        $storedName = $file->getRandomName();
        $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'medical_attachments';

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $file->move($targetDir, $storedName);

        $relativePath = 'uploads/medical_attachments/' . $storedName;
        $optimizedPath = $this->optimizeImageIfPossible($targetDir, $storedName, $mimeType);

        $payload = [
            'facility_id'    => (int) $this->request->getPost('facility_id'),
            'medicine_id'    => $this->nullableInt($this->request->getPost('medicine_id')),
            'batch_id'       => $this->nullableInt($this->request->getPost('batch_id')),
            'original_name'  => $file->getClientName(),
            'stored_name'    => $storedName,
            'mime_type'      => $mimeType,
            'size_kb'        => (int) ceil($file->getSize() / 1024),
            'path'           => $relativePath,
            'optimized_path' => $optimizedPath,
            'uploaded_by'    => $this->apiUser['user_id'] ?? null,
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        $model = new MedicalAttachmentModel();

        if (! $model->insert($payload)) {
            return $this->validationFailed($model->errors());
        }

        return $this->created($model->find((int) $model->getInsertID()), 'Attachment uploaded.');
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    private function optimizeImageIfPossible(string $targetDir, string $storedName, string $mimeType): ?string
    {
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'], true)) {
            return null;
        }

        $source = $targetDir . DIRECTORY_SEPARATOR . $storedName;
        $optimizedName = 'optimized_' . $storedName;
        $destination = $targetDir . DIRECTORY_SEPARATOR . $optimizedName;

        // The image service keeps uploaded proof files web-safe without adding
        // a third-party dependency, which is ideal for the syllabus scope.
        try {
            service('image')
                ->withFile($source)
                ->resize(1280, 1280, true, 'height')
                ->save($destination, 82);
        } catch (Throwable) {
            return null;
        }

        return 'uploads/medical_attachments/' . $optimizedName;
    }
}
