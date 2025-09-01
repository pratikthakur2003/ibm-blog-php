<?php
interface Auth {
    public function validateInput($data);
    public function process();
}
?>