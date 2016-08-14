<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FranMoreno\Silex\Twig\PagerfantaExtension;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

$ERROR_ENTERING_EMPTY_DESCRIPTION_LOG = "empty description is not granted. Please fill in with relevant content";
$ERASING_PREVIOUS_DESCRIPTION_START_TEXT = "previous description: ";
$ERASING_PREVIOUS_DESCRIPTION_END_TEXT = " has been deleted";
$INSERTING_NEW_DESCRIPTION_START_TEXT = "new description: ";
$INSERTING_NEW_DESCRIPTION_END_TEXT = " has been inserted";


$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

$app['twig']->addExtension(new PagerfantaExtension($app));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $user = $app['dao.user']->findUser($username, $password);
        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }
    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $todo = $app['dao.user']->findDescriptionById($id);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $todos = $app['dao.user']->findDescriptionByUser($user);
        $adapter = new ArrayAdapter($todos);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));

        return $app['twig']->render('todos.paginated.html', array(
            'pager' => $pagerfanta
        ));

    }
})
->value('id', null);


$app->get('/todo/{id}/json', function ($id) use ($app) {

    $user = $app['session']->get('user');

    if ($id){
        $todo = $app['dao.user']->findDescriptionById($id);
        $jsonData = json_encode($todo);
        $headers = array(
            'Content-Type' => 'application/json'
        );
        $response = new Response($jsonData, 200, $headers);
        return $response;
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    global $INSERTING_NEW_DESCRIPTION_START_TEXT;
    global $INSERTING_NEW_DESCRIPTION_END_TEXT;
    global $ERROR_ENTERING_EMPTY_DESCRIPTION_LOG;
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');
    if ($description != '' && (strlen(trim($description)) != 0)) {
        $todos = $app['dao.user']->addDescription($user_id, $description);
        $app['session']->getFlashBag()->add('textBeforeDescription', $INSERTING_NEW_DESCRIPTION_START_TEXT);
        $app['session']->getFlashBag()->add('description', $description);
        $app['session']->getFlashBag()->add('textAfterDescription', $INSERTING_NEW_DESCRIPTION_END_TEXT);
    } else {
        $app['session']->getFlashBag()->add('textBeforeDescription', 'please fill in description ');
        $app['session']->getFlashBag()->add('textAfterDescription', ' without any more empty text');
        error_log($ERROR_ENTERING_EMPTY_DESCRIPTION_LOG);
    }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    global $ERASING_PREVIOUS_DESCRIPTION_START_TEXT;
    global $ERASING_PREVIOUS_DESCRIPTION_END_TEXT;
    $description = $app['dao.user']->getDescription($id);
    $app['dao.user']->deleteDescription($id);
    error_log('flash bag...');
    $app['session']->getFlashBag()->add('textBeforeDescription', $ERASING_PREVIOUS_DESCRIPTION_START_TEXT);
    $app['session']->getFlashBag()->add('description', $description);
    $app['session']->getFlashBag()->add('textAfterDescription', $ERASING_PREVIOUS_DESCRIPTION_END_TEXT);

    return $app->redirect('/todo');
});