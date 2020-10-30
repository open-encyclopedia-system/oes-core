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

class AmsFE_Parser
{

    function parseClassMethod($method)
    {
        echo "Method: ", $method->name->name, "\n";

        foreach ($method->stmts as $stmt)
        {

            $expr = $stmt->expr;

            $nodeType = $expr->nodeType;

//                        print_r($expr);

            if ($nodeType == 'Expr_PropertyFetch') {
                $var = $expr->var->name;
                $attribute = $expr->name->name;
                echo "$var -> $attribute\n";
                continue;
            }

            if ($nodeType == 'Expr_MethodCall') {
                $methodName = $expr->name->name;
                if ($expr->var->nodeType == 'Expr_Variable') {
                    $var = $expr->var->name;
                    echo "$var -> $methodName ()\n";
                } else {
                    $attrib = $expr->var->name->name;
                    $var = $expr->var->var->name;
                    echo "$var -> $attrib -> $methodName ()\n";
                }
                continue;
            }

        }

    }

    function parseClass($class)
    {
        echo "Class: ", $class->name->name, "\n";
        foreach ($class->stmts as $stmt) {
            if ($stmt->nodeType != 'Stmt_ClassMethod') {
                continue;
            }
            $this->parseClassMethod($stmt);
        }
    }
    
    function parseClasses($data)
    {
        foreach ($data as $node)
        {
            if ($node->nodeType != 'Stmt_Class') {
                continue;
            }

            $this->parseClass($node);

        }


    }

}