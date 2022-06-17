<?php 
///////////// Create Model (app/Models)  (password data type must be char(60)) //////////////
namespace App\Models;
    use CodeIgniter\Database\ConnectionInterface;
    use CodeIgniter\Model;

class UserModel extends Model
{
protected $table = 'userlist';
protected $primaryKey = 'id';
protected $allowedFields = ['name', 'email', 'phone','password'];
}

//////////////// Create LoginControllers (app/Controllers) ///////////////
use App\Models\UserModel;
public function login(){
    $session = \Config\Services::session();  
    $data = [];
    $data['title'] = "Login";
    $model = new UserModel();
    $email = $this->request->getVar('email');
    $password = $this->request->getVar('password');
    $data = $model->where('email', $email)->first();
    //print_r($data); die;
    
    if($data){
        $pass = $data['password'];
        $authenticatePassword = password_verify($password, $pass);
       // print_r($authenticatePassword); die;
        if($authenticatePassword){
            $ses_data = [
                'id' => $data['id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'isLoggedIn' => TRUE
            ];
            $session->set($ses_data);
            return redirect()->to('/view');
        
        }else{
            $session->setFlashdata('message', 'Password is incorrect.');
            return redirect()->to('/');
        }
    }
    else{
        $session->setFlashdata('message', 'Email does not exist.');
        return redirect()->route('/');
    }
}

////////////////////// Create View /////////////////


<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('form'); ?>
<?php $session = \Config\Services::session(); ?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            
        <h2>Login in</h2>
                
                <?php if($session->getFlashdata('message')):?>
                    <div class="alert alert-warning">
                       <?= $session->getFlashdata('message') ?>
                    </div>
                <?php endif;?>
                <form action="<?php echo base_url('loginAuth'); ?>" method="post">
                    <div class="form-group mb-3">
                        <input type="email" name="email" placeholder="Email" value="<?= set_value('email') ?>" class="form-control" >
                    </div>
                    <div class="form-group mb-3">
                        <input type="password" name="password" placeholder="Password" class="form-control" >
                    </div>
                    
                    <div class="d-grid">
                         <button type="submit" class="btn btn-success">Signin</button>
                    </div>     
                </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>


//////////// app/Config/Filters.php //////////

public $aliases = [
    'csrf'          => CSRF::class,
    'toolbar'       => DebugToolbar::class,
    'honeypot'      => Honeypot::class,
    'invalidchars'  => InvalidChars::class,
    'secureheaders' => SecureHeaders::class,
    'authGuard' => \App\Filters\AuthGuard::class, // Adding This Line
];

//////////// app/Filters/AuthGuard.php --- Create /////////////

<?php 
namespace App\Filters;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthGuard implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn'))
        {
            return redirect()->to('/');
        }
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        
    }
}


//////////// Adding Filter in routes (app/Config/Routs.php) //////////////

$routes->get('/', 'Home::index');
$routes->get('/register', 'Register::index');
$routes->post('/create', 'Register::create');
$routes->get('/view', 'Register::show',['filter' => 'authGuard']); // Adding Filter
$routes->get('/delete/(:num)','Register::delete/$1'); // you can adding filter here
$routes->get('/edit/(:num)','Register::edit/$1');
$routes->post('/update', 'Register::updatedata');
$routes->post('/loginAuth', 'Register::login');
