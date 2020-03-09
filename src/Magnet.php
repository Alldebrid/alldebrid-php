<?php

namespace Alldebrid;

class Magnet {

public $alldebrid;

public $type;
public $magnet;

public $id;

public $status;

public $links;

public function __construct(Alldebrid $alldebrid, $magnet) {
    $this->alldebrid = $alldebrid;

    if($magnet == (int) $magnet AND $magnet > 0) {
        $this->id = $magnet;
        $this->status();
    } else if(strpos($magnet, 'magnet:') === 0) {
        $this->type = 'magnet';
        $this->magnet = $magnet;
    } else {
        $this->type = 'file';
        $this->magnet = $magnet;
    }
}

public function isRunning() {
    if($this->id === null) {
        return $this->alldebrid->response('Init by magnet ID or upload to check magnet status', 'LIB_ERROR');
    }

    if($this->status != null AND $this->status['statusCode'] > 3) {
        return $this->alldebrid->response(false);
    }
    
    [ $response, $error ] =  $this->alldebrid->magnetStatus($this->id);

    if($error) $this->alldebrid->response($response, $error);

    if($this->status['statusCode'] < 4) {
        return $this->alldebrid->response(true);
    }

    return $this->alldebrid->response(false);
}

public function isReady() {
    if($this->id === null) {
        return $this->alldebrid->response('Init by magnet ID or upload to check magnet status', 'LIB_ERROR');
    }

    if($this->status != null AND $this->status['statusCode'] == 4) {
        return $this->alldebrid->response(true);
    }
    if($this->status != null AND $this->status['statusCode'] > 4) {
        return $this->alldebrid->response(false);
    }
    
    [ $response, $error ] =  $this->alldebrid->magnetStatus($this->id);

    if($error) $this->alldebrid->response($response, $error);

    if($this->status['statusCode'] == 4) {
        return $this->alldebrid->response(true);
    }

    return $this->alldebrid->response(false);
}

public function isError() {
    if($this->id === null) {
        return $this->alldebrid->response('Init by magnet ID or upload to check magnet status', 'LIB_ERROR');
    }

    if($this->status != null AND $this->status['statusCode'] == 4) {
        return $this->alldebrid->response(false);
    }
    if($this->status != null AND $this->status['statusCode'] > 4) {
        return $this->alldebrid->response(true);
    }
    
    [ $response, $error ] =  $this->alldebrid->magnetStatus($this->id);

    if($error) $this->alldebrid->response($response, $error);

    if($this->status['statusCode'] > 4) {
        return $this->alldebrid->response(true);
    }

    return $this->alldebrid->response(false);
}

public function links() {
    if($this->id === null) {
        return $this->alldebrid->response('Init by magnet ID or upload to check magnet status', 'LIB_ERROR');
    }

    if($this->status != null AND $this->status['statusCode'] == 4) {
        return $this->alldebrid->response('Magnet is not ready', 'LIB_ERROR');
    }

    return $this->status['links'];
}

public function instant() {
    if($this->type !== 'magnet') {
        return $this->alldebrid->response('Instant method only valid for magnets', 'LIB_ERROR');
    }
    
    return $this->alldebrid->responseFlatten($this->alldebrid->magnetInstant($this->magnet));
}

public function upload() {
    if($this->type == 'magnet') {
        [ $response, $error ] =  $this->alldebrid->magnetUpload($this->magnet);
    } elseif($this->type == 'file') {
        [ $response, $error ] =  $this->alldebrid->magnetUploadFile($this->magnet);
    } else {
        return $this->alldebrid->response('$magnet->upload() can only be done when magnet or file was given in magnet() init call', 'LIB_ERROR');
    }

    if($error) return $this->alldebrid->response($response, $error);

    $this->id = $response['id'];

    return $this->alldebrid->response($response);
}

public function status() {
    if($this->id === null) {
        return $this->alldebrid->response('Init by magnet ID or upload to check magnet status', 'LIB_ERROR');
    }
    
    [ $response, $error ] =  $this->alldebrid->magnetStatus($this->id);

    if($error) $this->alldebrid->response($response, $error);

    $this->status = $response;

    if($this->status['statusCode'] == 4) {
        $this->links = $this->status['links'];
    }

    return $this->alldebrid->response($response);
}

public function delete() {
    if($this->id === null) {		
        return $this->alldebrid->response('Init by magnet ID or upload to check magnet status', 'LIB_ERROR');
    }
    
    [ $response, $error ] =  $this->alldebrid->magnetDelete($this->id);

    if($error) $this->alldebrid->response($response, $error);

    $this->id = null;
    return $this->alldebrid->response($response);
}

public function restart() {
    if($this->id === null) {
        return $this->alldebrid->response('Init by magnet ID or upload to check magnet status', 'LIB_ERROR');
    }
    
    return $this->alldebrid->responseFlatten($this->alldebrid->magnetRestart($this->id));
}
}