<?php
namespace commons\framework;

use RuntimeException;
use finfo;
class Upload {

    public function upImage($key)
    {
        try {
            //TODO $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (
                !isset($_FILES[$key]['error']) ||
                is_array($_FILES[$key]['error'])
            ) {
                throw new RuntimeException('Invalid parameters.');
            }
            // Check $_FILES[$key]['error'] value.
            switch ($_FILES[$key]['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('没有找到'.$key."的文件");
                case UPLOAD_ERR_INI_SIZE:
                    throw new RuntimeException('错误的初始化大小');
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('错误的文件大小.');
                default:
                    throw new RuntimeException('未知错误.');
            }
            if ($_FILES[$key]['size'] > 2 * 1024 * 1024) {
                throw new RuntimeException('文件大小超出.');
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                    $finfo->file($_FILES[$key]['tmp_name']),
                    array(
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                    ),
                    true
                )) {
                throw new RuntimeException('错误的文件格式.');
            }
            $r['path'] = sprintf('/uploads/%s.%s',
                sha1_file($_FILES[$key]['tmp_name']),
                $ext
            );
            $save_path = ROOT.$r['path'];
            $return['file'] = $_FILES[$key];
            if (!move_uploaded_file( $_FILES[$key]['tmp_name'], $save_path)) {
                throw new RuntimeException('移动文件出错.');
            }
            return $r;
        } catch (RuntimeException $e) {
            throw  $e;
        }
    }


}