<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class Network extends Constraint
{

    public $message = 'Network "{{ string }}" does not look like a valid CIDR!';

}
