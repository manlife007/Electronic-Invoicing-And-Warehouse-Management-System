<?php

/*
 * @Author:    Kiril Kirkov
 *  Github:    https://github.com/kirilkirkov
 */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Items extends USER_Controller
{

    private $num_rows = 2;
    private $editId;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('ItemsModel', 'NewInvoiceModel'));
    }

    public function index($page = 0)
    {
        $data = array();
        $head = array();
        $head['title'] = 'Administration - Home';
        $this->postChecker();
        $rowscount = $this->ItemsModel->countItems($_GET);
        $data['items'] = $this->ItemsModel->getItems($this->num_rows, $page);
        $data['linksPagination'] = pagination('user/items', $rowscount, $this->num_rows, 3);
        $this->render('items/index', $head, $data);
        $this->saveHistory('Go to items page');
    }

    public function addItem($id = 0)
    {
        $data = array();
        $head = array();
        $head['title'] = 'Administration - Home';
        $this->editId = $id;
        $this->postChecker();
        if ($id > 0) {
            $result = $this->ItemsModel->getItemInfo($id);
            if (empty($result)) {
                show_404();
            }
            $_POST = $result;
        }
        $data['quantityTypes'] = $this->NewInvoiceModel->getAllQuantityTypes();
        $data['currencies'] = $this->NewInvoiceModel->getCurrencies();
        $data['editId'] = $id;
        $this->render('items/additem', $head, $data);
        $this->saveHistory('Go to add item page');
    }

    private function postChecker()
    {
        if (isset($_POST['name'])) {
            $this->setItem();
        }
        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'delete') {
                $this->deleteSelectedItems($_POST['ids']);
            }
        }
    }

    private function deleteSelectedItems($ids)
    {
        $this->ItemsModel->multipleDeleteItems($ids);
        redirect(lang_url('user/items'));
    }

    private function setItem()
    {
        $isValid = $this->validateItem();
        if ($isValid === true) {
            $_POST['editId'] = $this->editId;
            $this->ItemsModel->setItem($_POST);
            $this->saveHistory('Add item - ' . $_POST['name']);
            redirect(lang_url('user/items'));
        } else {
            $this->session->set_flashdata('resultAction', $isValid);
            if ($this->editId > 0) {
                redirect(lang_url('user/edit/item/' . $this->editId));
            } else {
                redirect(lang_url('user/add/item'));
            }
        }
    }

    private function validateItem()
    {
        $errors = array();
        if (mb_strlen(trim($_POST['name'])) == 0) {
            $errors[] = lang('err_create_item_name');
        }
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    public function deleteItem($id)
    {
        $this->ItemsModel->deleteItem($id);
        redirect(lang_url('user/items'));
    }

}
