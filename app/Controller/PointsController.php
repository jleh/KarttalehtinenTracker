<?php
class PointsController extends AppController {
    public $helpers = array('Html', 'Session', 'Form');
    public $components = array('Session', 'RequestHandler');
    
    public function index() {
        $this->set('points', $this->Point->find('all'));
    }
    
    public function add() {
        if($this->request->is('post')) {
            $this->Point->create();
            if($this->Point->save($this->request->data)) {
                $this->Session->setFlash("Saved.");
                $this->redirect(array('action' => 'index'));
            }
            else {
                $this->Session->setFlash("Failed");
            }
        }
    }
    
    public function route(){
        $points = $this->Point->find('all');
        $coords;
        
        foreach ($points as $point) {
            $coords[] = array((float) $point['Point']['lat'],
                              (float) $point['Point']['lng']);
        }
        $this->set('message', $coords);
        $this->render('json');
    }
    
    public function dummy() {
        $this->set('message', array());
        $this->render('json');
    }
}
?>