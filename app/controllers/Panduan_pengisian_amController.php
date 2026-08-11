<?php
/**
 * Panduan Page Controller
 * @category  Controller
 */

class Panduan_pengisian_amController extends SecureController
{
    public function index()
    {
        return $this->render_view('panduan_pengisian_am/view.php');
    }
}