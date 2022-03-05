<?php

namespace App\Controllers;

class Pages extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function view($page = 'Home')
	{
		$page = ucfirst($page);
		if (! is_file(APPPATH . 'Views/Pages/' . $page . '.php')) {
			// Whoops, we don't have a page for that!
			throw new \CodeIgniter\Exceptions\PageNotFoundException($page);
		}

		$data['title'] = $page;

		echo view('Templates/Header', $data);
		echo view('Pages/' . $page, $data);
		echo view('Templates/Footer', $data);
	}
}