<?php declare(strict_types=1);

namespace Controller;

use GBV\DB;
use GBV\NotFoundException;
use GBV\RDA\Field;

/**
 * Controller for PICA+ Schema API.
 */
class PICA extends JSON
{
    public function data($f3, $params)
    {
        $db = new DB($f3['configFile']);

        $type = $params['type'];
        $path = $params['*'] ?? '';

        if ($type == 'rda') {
            try {
                $field = new Field($path, $db);

                if ($field->isPica3()) {
                    $f3->reroute("/pica/$type/schema/" . $field->getField());
                }

                return $field->getData();
            } catch (NotFoundException $e) {
                return;
            }
        }
    }
}
