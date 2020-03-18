<?php

namespace Alldebrid\tests\units;

use atoum;

/**
 * @engine inline
 */
class Alldebrid extends atoum {

    public function testBadAuth () {

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'bad_token');
        $response = $alldebrid->user();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->string[0]->isEqualTo('The auth apikey is invalid')
                ->string[1]->isEqualTo('AUTH_BAD_APIKEY')
        ;
    }

    public function testSetApikey () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting');
        $alldebrid->setApikey('PUT-A-VALID-APIKEY-HERE');

        $response = $alldebrid->user();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['username', 'email', 'isPremium', 'premiumUntil', 'lang', 'preferedDomain']);
                });
        ;
    }

    public function testHosts () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->hosts();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['hosts', 'streams', 'redirectors']);
                });
        ;
    }

    public function testHostsPriority () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->hostsPriority();
            
        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKey('hosts');
                });
        ;
    }

    public function testUser () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->user();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['username', 'email', 'isPremium', 'premiumUntil', 'lang', 'preferedDomain']);
                });
        ;
    }

    public function testLinkType () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->linkType('https://example.com/testing');

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->variable[0]->isEqualTo('hosts')
        ;
    }

    public function testLinkIsSupported () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response =  $alldebrid->linkIsSupported('https://example.com/testing');

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->variable[0]->isEqualTo(true)
        ;
    }

    public function testLinkInfos () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->linkInfos('https://example.com/testing');

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['link', 'filename', 'size', 'host', 'hostDomain']);
                });
        ;
    }

    public function testLinkUnlock () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->linkUnlock('https://example.com/testing');

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['link', 'host', 'filename', 'filesize', 'hostDomain']);
                });
        ;
    }

    public function testLinkStream () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        [ $unlock, $error ] = $alldebrid->linkUnlock('https://example.com/streaming');

        $response = $alldebrid->linkStream($unlock['id'], $unlock['streams'][0]['id']);

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['link', 'filename', 'filesize']);
                });
        ;
    }

    public function testLinkStreamAuto () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $alldebrid->options['autoUnlockBestStreamQuality'] = true;

        $response = $alldebrid->linkUnlock('https://example.com/streaming');

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['link', 'filename', 'filesize']);
                });
        ;
    }

    public function testLinkDelayed () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        [ $delayed, $error ] = $alldebrid->linkUnlock('https://example.com/delayed');

        $response = $alldebrid->linkDelayed($delayed['delayed']);

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['status', 'time_left']);
                });
        ;
    }

    public function testLinkWaitForDelayed () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        [ $delayed, $error ] = $alldebrid->linkUnlock('https://example.com/delayed');

        $response = $alldebrid->linkWaitForDelayed($delayed['delayed']);

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['status', 'time_left', 'link']);
                });
        ;
    }


    public function testUserLinks () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->userLinks();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->isNotEmpty()
                        ->child[0](function($child) {
                             $child
                                ->hasKeys(['link', 'filename', 'size', 'date', 'host']);
                        });
                });
        ;
    }

    public function testUserLinksSave () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->userLinksSave('https://example.com/testingLinksSave');

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKey('message');
                });
        ;
    }

    public function testUserLinksDelete () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->userLinksDelete('https://example.com/testingLinksSave');

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKey('message');
                });
        ;
    }

    public function testUserHistory () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->userHistory();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->isNotEmpty()
                        ->child[0](function($child) {
                             $child
                                ->hasKeys(['link', 'filename', 'size', 'date', 'host']);
                        });
                });
        ;
    }

    public function testUserHistoryDelete () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->userHistoryDelete();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKey('message');
                });
        ;
    }

    public function testPinCode () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting');
        $response = $alldebrid->pinGet();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['pin', 'check', 'expires_in']);
                });
        ;
    }

    public function testPinCheck () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting');
        [ $code, $error ] = $alldebrid->pinGet();

        $response = $alldebrid->pinCheck($code['pin'], $code['check']);

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['activated', 'expires_in']);
                });
        ;
    }

    public function testMagnetUpload () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->magnetUpload('magnet:?xt=urn:btih:286d2e5b4f8369855328336ac1263ae02a7a60d5');

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['magnet', 'hash', 'name', 'size', 'ready', 'id']);
                });
        ;
    }

    public function testMagnetStatus () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');

        [ $magnet, $error ] = $alldebrid->magnetUpload('magnet:?xt=urn:btih:286d2e5b4f8369855328336ac1263ae02a7a60d5');

        $response = $alldebrid->magnetStatus($magnet['id']);

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['id', 'filename', 'size', 'status', 'statusCode', 'downloaded', 'uploaded', 'seeders', 'downloadSpeed', 'uploadSpeed', 'uploadDate', 'links']);
                });
        ;
    }

    public function testMagnetDelete () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');

        [ $magnet, $error ] = $alldebrid->magnetUpload('magnet:?xt=urn:btih:286d2e5b4f8369855328336ac1263ae02a7a60d5');

        $response = $alldebrid->magnetDelete($magnet['id']);

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKey('message');
                });
        ;
    }

    public function testMagnetRestart () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');

        $response = $alldebrid->magnetRestart(42);

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKey('message');
                });
        ;
    }

    public function testMagnetInstant () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');

        $response = $alldebrid->magnetInstant('magnet:?xt=urn:btih:286d2e5b4f8369855328336ac1263ae02a7a60d5');

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['magnet', 'hash', 'instant']);
                });
        ;
    }


    public function testError () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $response = $alldebrid->error('GENERIC');

        $this->array($response)
            ->hasSize(2)
            ->values
                ->string[0]->isEqualTo('An orror occured')
                ->string[1]->isEqualTo('GENERIC')
        ;
    }

    public function testException () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');
        $alldebrid->setErrorMode('exception');

        $this->exception(
            function() use($alldebrid) {
                $alldebrid->error('GENERIC');
            }
        )->hasMessage('GENERIC : An orror occured'); // passes
    }

    public function testPin () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting');

        $response = $alldebrid->pin();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->string[0]
                    ->hasLength(4)
                    ->matches('#^[0-9A-Z]{4}$#')
        ;

        $response = $alldebrid->isLoggued();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->boolean[0]->isEqualTo(false)
        ;

        $response = $alldebrid->waitForPin();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->boolean[0]->isEqualTo(true)
        ;

        $this->string($alldebrid->apikey)->hasLength(20);
    }

    public function testLink () {
        usleep(250000);

        $alldebrid = new \Alldebrid\Alldebrid('atoumTesting', 'PUT-A-VALID-APIKEY-HERE');

        $link = $alldebrid->link('https://example.com/streamDelayed');

        $response = $link->isSupported();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->variable[0]->isEqualTo(true)
        ;

        $response = $link->type();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->string[0]->isEqualTo('hosts')
        ;


        $response = $link->infos();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['link', 'filename', 'size', 'host', 'hostDomain']);
                });
        ;

        usleep(250000);

        $response = $link->unlock();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['link', 'host', 'filename', 'filesize', 'hostDomain', 'streams']);
                });
        ;

        $this->boolean($link->hasMultipleStreams)->isEqualTo(true);

        $response = $link->stream($response[0]['streams'][0]['id']);

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['delayed', 'filename', 'filesize']);
                });
        ;


        $this->boolean($link->isDelayed)->isEqualTo(true);

        $response = $link->waitFordelayed();

        $this->array($response)
            ->hasSize(2)
            ->values
                ->variable[1]->isEqualTo(null)
                ->child[0](function($child) {
                     $child
                        ->hasKeys(['status', 'time_left', 'link']);
                });
        ;
    }
}