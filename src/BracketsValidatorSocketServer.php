<?php

namespace Asil\Otus\HomeTask_2;

use Asil\Otus\HomeTask_1_1\SimpleBracketsProcessor;

class BracketsValidatorSocketServer extends AbstractSocketServer
{
    /**
     * @param string|null $msg
     *
     * @return string|null
     */
    protected function onClientSendMessage(string $msg = null)
    {
        $result = null;

        try {
            $bracketsProcessor = new SimpleBracketsProcessor();
            $result = $bracketsProcessor->isValidBracketLine($msg) ? 'String is valid' : 'String is invalid';
        } catch (\Throwable $e) {
            $result = $e->getMessage();
        }

        return $result;
    }
}