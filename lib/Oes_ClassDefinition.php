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

class Oes_ClassDefinition
{


    var $nameOfClass;

    var $namesOfExtendsClasses = [];

    var $namesOfInheritsClasses = [];

    var $listOfMethods = [];

    var $listOfConstants = [];

    var $listOfVariables = [];

    var $isInterfaceDefinition = false;

    var $classCommentBlock;

    var $isAbstract = false;

    function export($return = false)
    {
        if ($return) {
            ob_start();
        }

        $classOrInterfaceName = $this->isInterfaceDefinition ? "interface" : "class";

        if (!empty($this->classCommentBlock)) {

            if (is_array($this->classCommentBlock)) {
                echo "/**\n";
                foreach ($this->classCommentBlock as $line) {
                    echo "* $line\n";
                }
                echo "**/\n";
            } else {
                echo $this->classCommentBlock, "\n";
            }

        }

        if ($this->isAbstract()) {
            echo "abstract ";
        }
        
        echo "$classOrInterfaceName $this->nameOfClass";

        if (!empty($this->namesOfExtendsClasses)) {
            echo " extends ".implode(", ", $this->namesOfExtendsClasses);
        }

        if (!empty($this->namesOfInheritsClasses)) {
            echo " implements ".implode(", ", $this->namesOfInheritsClasses);
        }

        echo " {\n";

        foreach ($this->listOfConstants as $cons) {
            $cons->export();
        }

        foreach ($this->listOfVariables as $var) {
            $var->export();
        }

        /**
         * @var Oes_ClassMethod $method
         */
        foreach ($this->listOfMethods as $method) {
            $method->export();
        }

        echo "}\n";

        if ($return) {
            $exportdata = ob_get_clean();
            return $exportdata;
        }


    }

    /**
     * @param $name
     * @param $body
     * @param bool $argsAsFormattedStr
     * @param bool $isStatic
     * @param bool $returnByReferece
     * @return Oes_ClassMethod
     * @throws Exception
     */
    function & addMethod($name, $body, $argsAsFormattedStr = false, $isStatic = false, $returnByReferece = false, $bodyComment = '')
    {

        $meth = new Oes_ClassMethod();

        $meth->setMethodName($name);
        $meth->setBody($body);
        $meth->setArgumentsAsFormattedString($argsAsFormattedStr);
        $meth->setStaticMethod($isStatic);
        $meth->setReturnByReference($returnByReferece);
        $meth->setBodyComment($bodyComment);

        if (array_key_exists($name, $this->listOfMethods)) {
            throw new Exception("method ($name) exists in ($this->nameOfClass)");
        }

        $this->listOfMethods[$name] = $meth;

        return $meth;

    }

    function addConstant($name,$valueAsFormattedStr)
    {

        if (array_key_exists($name, $this->listOfConstants)) {
            throw new Exception("constant ($name) exists in ($this->nameOfClass) ");
//            throw new Exception("constant ($name) exists in ($this->nameOfClass) ".print_r($this->listOfConstants,true));
        }

        $cons = new Oes_ClassAttribute();
        $cons->setName($name);
        $cons->setValueAsFormattedString($valueAsFormattedStr);
        $cons->setConstant(true);

        $this->listOfConstants[$name] = $cons;

    }

    function addVariable($name,$valueAsFormattedStr,$isStatic=false,$comment='')
    {

        if (array_key_exists($name, $this->listOfVariables)) {
            throw new Exception("variable ($name) exists in ($this->nameOfClass)");
        }

        $cons = new Oes_ClassAttribute();
        $cons->setName($name);
        $cons->setValueAsFormattedString($valueAsFormattedStr);
        $cons->setStatic($isStatic);
        $cons->setComment($comment);

        $this->listOfVariables[$name] = $cons;

        return $cons;

    }

    /**
     * @return bool
     */
    public function isAbstract(): bool
    {
        return $this->isAbstract;
    }

    /**
     * @param bool $isAbstract
     */
    public function setIsAbstract(bool $isAbstract): void
    {
        $this->isAbstract = $isAbstract;
    }


    /**
     * @return bool
     */
    public function isInterfaceDefinition(): bool
    {
        return $this->isInterfaceDefinition;
    }

    /**
     * @param bool $isInterfaceDefinition
     */
    public function setIsInterfaceDefinition(bool $isInterfaceDefinition): void
    {
        $this->isInterfaceDefinition = $isInterfaceDefinition;
    }

    /**
     * @return mixed
     */
    public function getClassCommentBlock()
    {
        return $this->classCommentBlock;
    }

    /**
     * @param mixed $classCommentBlock
     */
    public function setClassCommentBlock($classCommentBlock): void
    {
        $this->classCommentBlock = $classCommentBlock;
    }


    /**
     * @return mixed
     */
    public function getNameOfClass()
    {
        return $this->nameOfClass;
    }

    /**
     * @param mixed $nameOfClass
     */
    public function setNameOfClass($nameOfClass): void
    {
        $this->nameOfClass = $nameOfClass;
    }

    /**
     * @return array
     */
    public function getNamesOfExtendsClasses(): array
    {
        return $this->namesOfExtendsClasses;
    }

    /**
     * @param array $namesOfExtendsClasses
     */
    public function setNamesOfExtendsClasses(array $namesOfExtendsClasses): void
    {
        $this->namesOfExtendsClasses = $namesOfExtendsClasses;
    }

    /**
     * @return array
     */
    public function getNamesOfInheritsClasses(): array
    {
        return $this->namesOfInheritsClasses;
    }

    /**
     * @param array $namesOfInheritsClasses
     */
    public function setNamesOfInheritsClasses(array $namesOfInheritsClasses): void
    {
        $this->namesOfInheritsClasses = $namesOfInheritsClasses;
    }

    /**
     * @return mixed
     */
    public function getListOfMethods()
    {
        return $this->listOfMethods;
    }

    /**
     * @param mixed $listOfMethods
     */
    public function setListOfMethods($listOfMethods): void
    {
        $this->listOfMethods = $listOfMethods;
    }

    /**
     * @return mixed
     */
    public function getListOfConstants()
    {
        return $this->listOfConstants;
    }

    /**
     * @param mixed $listOfConstants
     */
    public function setListOfConstants($listOfConstants): void
    {
        $this->listOfConstants = $listOfConstants;
    }

    /**
     * @return mixed
     */
    public function getListOfVariables()
    {
        return $this->listOfVariables;
    }

    /**
     * @param mixed $listOfVariables
     */
    public function setListOfVariables($listOfVariables): void
    {
        $this->listOfVariables = $listOfVariables;
    }

}
