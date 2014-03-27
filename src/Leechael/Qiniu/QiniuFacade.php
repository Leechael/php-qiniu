<?php

namespace Leechael\Qiniu;

use Illuminate\Support\Facades\Facade;

class QiniuFacade extends Facade {

    protected static function getFacadeAccessor() { return 'qiniu'; }

}