<?php
/*
 * This file is part of OES, the Open Encyclopedia System.
 *
 * Copyright (C) 2020 Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>

<?php

class Oes_ClassMethod
{

    var $methodName;

    var $staticMethod = false;

    var $returnType = false;

    var $body = "";

    var $argumentsAsFormattedString;

    var $returnByReference = false;

    var $bodyComment = "";


    function export($return = false)
    {

        if (!empty($this->bodyComment)) {

            if (is_array($this->bodyComment)) {
                echo "/**\n";
                foreach ($this->bodyComment as $line) {
                    echo "* $line\n";
                }
                echo "**/\n";
            } else {
                echo $this->bodyComment, "\n";
            }
        }

        echo "public";

        if ($this->isStaticMethod()) {
            echo " static ";
        }

        echo " function";

        if ($this->returnByReference) {
            echo " &";
        }

        echo " ", $this->methodName, "(";

        if (!empty($this->argumentsAsFormattedString)) {
            echo $this->argumentsAsFormattedString;
        }

        echo ")";

        if ($this->returnType) {
            echo " : ", $this->returnType;
        }

        echo " {\n";

        echo $this->body,"\n";

        echo "}\n\n";

    }

    /**
     * @return string
     */
    public function getBodyComment(): string
    {
        return $this->bodyComment;
    }

    /**
     * @param string $bodyComment
     */
    public function setBodyComment($bodyComment): void
    {
        $this->bodyComment = $bodyComment;
    }




    /**
     * @return bool
     */
    public function isReturnByReference(): bool
    {
        return $this->returnByReference;
    }

    /**
     * @param bool $returnByReference
     */
    public function setReturnByReference(bool $returnByReference): void
    {
        $this->returnByReference = $returnByReference;
    }

    /**
     * @return bool
     */
    public function isStaticMethod(): bool
    {
        return $this->staticMethod;
    }

    /**
     * @param bool $staticMethod
     */
    public function setStaticMethod(bool $staticMethod): void
    {
        $this->staticMethod = $staticMethod;
    }

    /**
     * @return mixed
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @param mixed $methodName
     */
    public function setMethodName($methodName): void
    {
        $this->methodName = $methodName;
    }

    /**
     * @return mixed
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param mixed $returnType
     */
    public function setReturnType($returnType): void
    {
        $this->returnType = $returnType;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getArgumentsAsFormattedString()
    {
        return $this->argumentsAsFormattedString;
    }

    /**
     * @param mixed $argumentsAsFormattedString
     */
    public function setArgumentsAsFormattedString($argumentsAsFormattedString): void
    {
        $this->argumentsAsFormattedString = $argumentsAsFormattedString;
    }


}