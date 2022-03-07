<?php

namespace App\Controllers;

use App\Models\NewsModel;
use MongoDB;
use MongoDB\Client;

class News extends BaseController
{
    public function index()
    {
        $model = model(NewsModel::class);

        $data = [
            'news'  => $model->getNews(),
            'title' => 'Arquivo de notícias',
        ];

        echo view('templates/header', $data);
        echo view('news/overview', $data);
        echo view('templates/footer', $data);
    }

    public function view($slug = null)
    {
        $model = model(NewsModel::class);

        $data['news'] = $model->getNews($slug);

        if (empty($data['news'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Não foi encontrado: ' . $slug);
        }

        $data['title'] = $data['news']['title'];
        $timer = \Config\Services::timer();
        $data['timer'] = $timer;
        
        $data['mongo'] = $this->_insertMongoData();

        echo view('templates/header', $data);
        echo view('news/view', $data);
        echo view('templates/footer', $data);
    }

    protected function _insertMongoData() {
        $client = new Client("mongodb://admin:123@localhost:27017");
        $collection = $client->salesgroup->beers;

        $result = $collection->insertOne( [ 'name' => 'Matheus', 'option_1' => 'test' ] );

        return "Inserted with Object ID '{$result->getInsertedId()}'";
    }

    public function create()
    {
        $model = model(NewsModel::class);

        if ($this->request->getMethod() === 'post' && $this->validate([
            'title' => 'required|min_length[3]|max_length[255]',
            'body'  => 'required',
        ])) {
            $model->save([
                'title' => $this->request->getPost('title'),
                'slug'  => url_title($this->request->getPost('title'), '-', true),
                'body'  => $this->request->getPost('body'),
            ]);

            echo view('news/success');
        } else {
            echo view('templates/header', ['title' => 'Create a news item']);
            echo view('news/create');
            echo view('templates/footer');
        }
    }
}