<?php

namespace App\Controllers;

use App\Models\NewsModel;
use App\Libraries\Mongo\MongoConnection;
use MongoDB\BSON\Regex;

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
        // $_mongoAdmin = new MongoConnection('admin');
        // $_mongoAdmin->setCollection('datakeys')->drop();
        $_mongo = new MongoConnection();
        // $_mongo->setCollection('users')->drop();
        $_mongo->setCollection('users');

        // $_mongo->collection->insertOne([
        //     'name' => 'Matheus',
        //     'enc_code_number' => 123, 
        //     'endereco' => 'Rua Santa Lúcia, 249', 
        //     'enc_documento' => '123456789'
        // ]);

        // $_mongo->collection->insertMany([
        //     [
        //         'name' => 'Julia',
        //         'enc_code_number' => 321,
        //         'endereco' => [
        //             'rua' => 'Dolores Duran',
        //             'enc_cep' => '13473661',
        //             'estado' => [
        //                 'enc_sigla' => 'SP',
        //                 'cidade' => 'Americana'
        //             ]
        //         ],
        //         'enc_documento' => '987654321'
        //     ],
        //     [
        //         'name' => 'João',
        //         'enc_code_number' => '951',
        //         'endereco' => 'Rua Pando Tando, nº 300',
        //         'enc_documento' => '852147963'
        //     ],
        //     [
        //         'name' => 'João',
        //         'enc_code_number' => '9511',
        //         'endereco' => 'Rua Pando Tando, nº 3000',
        //         'enc_documento' => '123456'
        //     ],
        // ]);

        $_mongo->setCollection('users')->updateMany(
            ['endereco.rua' => 'Dolores Duran'],
            ['$set' => ['endereco.enc_cep' => '1234567']]
        );

        return json_encode($_mongo->collection->find(
            // ['name' => new Regex('^jul', 'i')], ['projection' => ['_id' => 0]]
            // ['enc_documento' => '123456789'], ['projection' => ['_id' => 0]]
            ['endereco.estado.enc_sigla' => 'SP', 'endereco.estado.cidade' => 'Americana'], ['projection' => ['_id' => 0]]
        ), JSON_PRETTY_PRINT);
        
        // return json_encode(['ok' => 'ok'], JSON_PRETTY_PRINT);
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