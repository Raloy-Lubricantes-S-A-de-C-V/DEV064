###############################################################################
#
#   Class: NaturalDocs::Languages::ActionScript
#
###############################################################################
#
#   A subclass to handle the language variations of Flash ActionScript.
#
###############################################################################

# This file is part of Natural Docs, which is Copyright (C) 2003-2008 Greg Valure
# Natural Docs is licensed under the GPL

use strict;
use integer;

package NaturalDocs::Languages::ActionScript;

use base 'NaturalDocs::Languages::Advanced';


################################################################################
# Group: Constants and Types


#
#   Constants: XML Tag Type
#
#   XML_OPENING_TAG - The tag is an opening one, such as <tag>.
#   XML_CLOSING_TAG - The tag is a closing one, such as </tag>.
#   XML_SELF_CONTAINED_TAG - The tag is self contained, such as <tag />.
#
use constant XML_OPENING_TAG => 1;
use constant XML_CLOSING_TAG => 2;
use constant XML_SELF_CONTAINED_TAG => 3;


################################################################################
# Group: Package Variables

#
#   hash: classModifiers
#   An existence hash of all the acceptable class modifiers.  The keys are in all lowercase.
#
my %classModifiers = ( 'dynamic' => 1,
                                   'intrinsic' => 1,
                                   'final' => 1,
                                   'internal' => 1,
                                   'public' => 1 );

#
#   hash: memberModifiers
#   An existence hash of all the acceptable class member modifiers.  The keys are in all lowercase.
#
my %memberModifiers = ( 'public' => 1,
                                        'private' => 1,
                                        'protected' => 1,
                                        'static' => 1,
                                        'internal' => 1,
                                        'override' => 1 );


#
#   hash: declarationEnders
#   An existence hash of all the tokens that can end a declaration.  This is important because statements don't require a semicolon
#   to end.  The keys are in all lowercase.
#
my %declarationEnders = ( ';' => 1,
                                        '}' => 1,
                                        '{' => 1,
                                        'public' => 1,
                                        'private' => 1,
                                        'protected' => 1,
                                        'static' => 1,
                                        'internal' => 1,
                                        'dynamic' => 1,
                                        'intrinsic' => 1,
                                        'final' => 1,
                                        'override' => 1,
                                        'class' => 1,
                                        'interface' => 1,
                                        'var' => 1,
                                        'function' => 1,
                                        'const' => 1,
                                        'namespace' => 1,
                                        'import' => 1 );


#
#   var: isEscaped
#   Whether the current file being parsed uses escapement.
#
my $isEscaped;



################################################################################
# Group: Interface Functions


#
#   Function: PackageSeparator
#   Returns the package separator symbol.
#
sub PackageSeparator
    {  return '.';  };


#
#   Function: EnumValues
#   Returns the <EnumValuesType> that describes how the language handles enums.
#
sub EnumValues
    {  return ::ENUM_GLOBAL();  };


#
#   Function: ParseParameterLine
#   Parses a prototype parameter line and returns it as a <NaturalDocs::Languages::Prototype::Parameter> object.
#
sub ParseParameterLine #(line)
    {
    my ($self, $line) = @_;

    if ($line =~ /^ ?\.\.\.\ (.+)$/)
        {
        # This puts them in the wrong fields as $1 should be the name and ... should be the type.  However, this is necessary
        # because the order in the source is reversed from other parameter declarations and it's more important for the output
        # to match the source.
        return NaturalDocs::Languages::Prototype::Parameter->New($1, undef, '...', undef, undef, undef);
        }
    else
        {  return $self->ParsePascalParameterLine($line);  };
    };


#
#   Function: TypeBeforeParameter
#   Returns whether the type appears before the parameter in prototypes.
#
sub TypeBeforeParameter
    {  return 0;  };


#
#   Function: PreprocessFile
#
#   If the file is escaped, strips out all unescaped code.  Will translate any unescaped comments into comments surrounded by
#   "\x1C\x1D\x1E\x1F" and "\x1F\x1E\x1D" characters, so chosen because they are the same character lengths as <!-- and -->
#   and will not appear in normal code.
#
sub PreprocessFile
    {
    my ($self, $lines) = @_;

    if (!$isEscaped)
        {  return;  };

    use constant MODE_UNESCAPED_REGULAR => 1;
    use constant MODE_UNESCAPED_PI => 2;
    use constant MODE_UNESCAPED_CDATA => 3;
    use constant MODE_UNESCAPED_COMMENT => 4;
    use constant MODE_ESCAPED_UNKNOWN_CDATA => 5;
    use constant MODE_ESCAPED_CDATA => 6;
    use constant MODE_ESCAPED_NO_CDATA => 7;

    my $mode = MODE_UNESCAPED_REGULAR;

    for (my $i = 0; $i < scalar @$lines; $i++)
        {
        my @tokens = split(/(<[ \t]*\/?[ \t]*mx:Script[^>]*>|<\?|\?>|<\!--|-->|<\!\[CDATA\[|\]\]\>)/, $lines->[$i]);
        my $newLine;

        foreach my $token (@tokens)
            {
            if ($mode == MODE_UNESCAPED_REGULAR)
                {
                if ($token eq '<?')
                    {  $mode = MODE_UNESCAPED_PI;  }
                elsif ($token eq '<![CDATA[')
                    {  $mode = MODE_UNESCAPED_CDATA;  }
                elsif ($token eq '<!--')
                    {
                    $mode = MODE_UNESCAPED_COMMENT;
                    $newLine .= "\x1C\x1D\x1E\x1F";
                    }
                elsif ($token =~ /^<[ \t]*mx:Script/)
                    {  $mode = MODE_ESCAPED_UNKNOWN_CDATA;  };
                }

            elsif ($mode == MODE_UNESCAPED_PI)
                {
                if ($token eq '?>')
                    {  $mode = MODE_UNESCAPED_REGULAR;  };
                }

            elsif ($mode == MODE_UNESCAPED_CDATA)
                {
                if ($token eq ']]>')
                    {  $mode = MODE_UNESCAPED_REGULAR;  };
                }

            elsif ($mode == MODE_UNESCAPED_COMMENT)
                {
                if ($token eq '-->')
                    {
                    $mode = MODE_UNESCAPED_REGULAR;
                    $newLine .= "\x1F\x1E\x1D";
                    }
                else
                    {  $newLine .= $token;  };
                }

            elsif ($mode == MODE_ESCAPED_UNKNOWN_CDATA)
                {
                if ($token eq '<![CDATA[')
                    {  $mode = MODE_ESCAPED_CDATA;  }
                elsif ($token =~ /^<[ \t]*\/[ \t]*mx:Script/)
                    {
                    $mode = MODE_UNESCAPED_REGULAR;
                    $newLine .= '; ';
                    }
                elsif ($token !~ /^[ \t]*$/)
                    {
                    $mode = MODE_ESCAPED_NO_CDATA;
                    $newLine .= $token;
                    };
                }

            elsif ($mode == MODE_ESCAPED_CDATA)
                {
                if ($token eq ']]>')
                    {
                    $mode = MODE_UNESCAPED_REGULAR;
                    $newLine .= '; ';
                    }
                else
                    {  $newLine .= $token;  };
                }

            else #($mode == MODE_ESCAPED_NO_CDATA)
                {
                if ($token =~ /^<[ \t]*\/[ \t]*mx:Script/)
                    {
                    $mode = MODE_UNESCAPED_REGULAR;
                    $newLine .= '; ';
                    }
                else
                    {  $newLine .= $token;  };
                };

            };

        $lines->[$i] = $newLine;
        };
    };


#
#   Function: ParseFile
#
#   Parses the passed source file, sending comments acceptable for documentation to <NaturalDocs::Parser->OnComment()>.
#
#   Parameters:
#
#       sourceFile - The <FileName> to parse.
#       topicList - A reference to the list of <NaturalDocs::Parser::ParsedTopics> being built by the file.
#
#   Returns:
#
#       The array ( autoTopics, scopeRecord ).
#
#       autoTopics - An arrayref of automatically generated topics from the file, or undef if none.
#       scopeRecord - An arrayref of <NaturalDocs::Languages::Advanced::ScopeChanges>, or undef if none.
#
sub ParseFile #(sourceFile, topicsList)
    {
    my ($self, $sourceFile, $topicsList) = @_;

    # The \x1# comment symbols are inserted by PreprocessFile() to stand in for XML comments in escaped files.
    my @parseParameters = ( [ '//' ], [ '/*', '*/', "\x1C\x1D\x1E\x1F", "\x1F\x1E\x1D" ], [ '///' ], [ '/**', '*/' ] );

    my $extension = lc(NaturalDocs::File->ExtensionOf($sourceFile));
    $isEscaped = ($extension eq 'mxml');

    $self->ParseForCommentsAndTokens($sourceFile, @parseParameters);

    my $tokens = $self->Tokens();
    my $index = 0;
    my $lineNumber = 1;

    while ($index < scalar @$tokens)
        {
        if ($self->TryToSkipWhitespace(\$index, \$lineNumber) ||
            $self->TryToGetImport(\$index, \$lineNumber) ||
            $self->TryToGetClass(\$index, \$lineNumber) ||
            $self->TryToGetFunction(\$index, \$lineNumber) ||
            $self->TryToGetVariable(\$index, \$lineNumber) )
            {
            # The functions above will handle everything.
            }

        elsif ($tokens->[$index] eq '{')
            {
            $self->StartScope('}', $lineNumber, undef, undef, undef);
            $index++;
            }

        elsif ($tokens->[$index] eq '}')
            {
            if ($self->ClosingScopeSymbol() eq '}')
                {  $self->EndScope($lineNumber);  };

            $index++;
            }

        else
            {
            $self->SkipToNextStatement(\$index, \$lineNumber);
            };
        };


    # Don't need to keep these around.
    $self->ClearTokens();


    my $autoTopics = $self->AutoTopics();

    my $scopeRecord = $self->ScopeRecord();
    if (defined $scopeRecord && !scalar @$scopeRecord)
        {  $scopeRecord = undef;  };

    return ( $autoTopics, $scopeRecord );
    };



################################################################################
# Group: Statement Parsing Functions
# All functions here assume that the current position is at the beginning of a statement.
#
# Note for developers: I am well aware that the code in these functions do not check if we're past the end of the tokens as
# often as it should.  We're making use of the fact that Perl will always return undef in these cases to keep the code simpler.


#
#   Function: TryToGetIdentifier
#
#   Determines whether the position is at an identifier, and if so, skips it and returns the complete identifier as a string.  Returns
#   undef otherwise.
#
#   Parameters:
#
#       indexRef - A reference to the current token index.
#       lineNumberRef - A reference to the current line number.
#       allowStar - If set, allows the last identifier to be a star.
#
sub TryToGetIdentifier #(indexRef, lineNumberRef, allowStar)
    {
    my ($self, $indexRef, $lineNumberRef, $allowStar) = @_;
    my $tokens = $self->Tokens();

    my $index = $$indexRef;

    use constant MODE_IDENTIFIER_START => 1;
    use constant MODE_IN_IDENTIFIER => 2;
    use constant MODE_AFTER_STAR => 3;

    my $identifier;
    my $mode = MODE_IDENTIFIER_START;

    while ($index < scalar @$tokens)
        {
        if ($mode == MODE_IDENTIFIER_START)
            {
            if ($tokens->[$index] =~ /^[a-z\$\_]/i)
                {
                $identifier .= $tokens->[$index];
                $index++;

                $mode = MODE_IN_IDENTIFIER;
                }
            elsif ($allowStar && $tokens->[$index] eq '*')
                {
                $identifier .= '*';
                $index++;

                $mode = MODE_AFTER_STAR;
                }
            else
                {  return undef;  };
            }

        elsif ($mode == MODE_IN_IDENTIFIER)
            {
            if ($tokens->[$index] eq '.')
                {
                $identifier .= '.';
                $index++;

                $mode = MODE_IDENTIFIER_START;
                }
            elsif ($tokens->[$index] =~ /^[a-z0-9\$\_]/i)
                {
                $identifier .= $tokens->[$index];
                $index++;
                }
            else
                {  last;  };
            }

        else #($mode == MODE_AFTER_STAR)
            {
            if ($tokens->[$index] =~ /^[a-z0-9\$\_\.]/i)
                {  return undef;  }
            else
                {  last;  };
            };
        };

    # We need to check again because we may have run out of tokens after a dot.
    if ($mode != MODE_IDENTIFIER_START)
        {
        $$indexRef = $index;
        return $identifier;
        }
    else
        {  return undef;  };
    };


#
#   Function: TryToGetImport
#
#   Determines whether the position is at a import statement, and if so, adds it as a Using statement to the current scope, skips
#   it, and returns true.
#
sub TryToGetImport #(indexRef, lineNumberRef)
    {
    my ($self, $indexRef, $lineNumberRef) = @_;
    my $tokens = $self->Tokens();

    my $index = $$indexRef;
    my $lineNumber = $$lineNumberRef;

    if ($tokens->[$index] ne 'import')
        {  return undef;  };

    $index++;
    $self->TryToSkipWhitespace(\$index, \$lineNumber);

    my $identifier = $self->TryToGetIdentifier(\$index, \$lineNumber, 1);
    if (!$identifier)
        {  return undef;  };


    # Currently we implement importing by stripping the last package level and treating it as a using.  So "import p1.p2.p3" makes
    # p1.p2 the using path, which is over-tolerant but that's okay.  "import p1.p2.*" is treated the same way, but in this case it's
    # not over-tolerant.  If there's no dot, there's no point to including it.

    if (index($identifier, '.') != -1)
        {
        $identifier =~ s/\.[^\.]+$//;
        $self->AddUsing( NaturalDocs::SymbolString->FromText($identifier) );
        };

    $$indexRef = $index;
    $$lineNumberRef = $lineNumber;

    return 1;
    };


#
#   Function: TryToGetClass
#
#   Determines whether the position is at a class declaration statement, and if so, generates a topic for it, skips it, and
#   returns true.
#
#   Supported Syntaxes:
#
#       - Classes
#       - Interfaces
#       - Classes and interfaces with _global
#
sub TryToGetClass #(indexRef, lineNumberRef)
    {
    my ($self, $indexRef, $lineNumberRef) = @_;
    my $tokens = $self->Tokens();

    my $index = $$indexRef;
    my $lineNumber = $$lineNumberRef;

    my @modifiers;

    while ($tokens->[$index] =~ /^[a-z]/i &&
              exists $classModifiers{lc($tokens->[$index])} )
        {
        push @modifiers, lc($tokens->[$index]);
        $index++;

        $self->TryToSkipWhitespace(\$index, \$lineNumber);
        };

    my $type;

    if ($tokens->[$index] eq 'class' || $tokens->[$index] eq 'interface')
        {
        $type = $tokens->[$index];

        $index++;
        $self->TryToSkipWhitespace(\$index, \$lineNumber);
        }
    else
        {  return undef;  };

    my $className = $self->TryToGetIdentifier(\$index, \$lineNumber);

    if (!$className)
        {  return undef;  };

    $self->TryToSkipWhitespace(\$index, \$lineNumber);

    my @parents;

    if ($tokens->[$index] eq 'extends')
        {
        $index++;
        $self->TryToSkipWhitespace(\$index, \$lineNumber);

        my $parent = $self->TryToGetIdentifier(\$index, \$lineNumber);
        if (!$parent)
            {  return undef;  };

        push @parents, $parent;

        $self->TryToSkipWhitespace(\$index, \$lineNumber);
        };

    if ($type eq 'class' && $tokens->[$index] eq 'implements')
        {
        $index++;
        $self->TryToSkipWhitespace(\$index, \$lineNumber);

        for (;;)
            {
            my $parent = $self->TryToGetIdentifier(\$index, \$lineNumber);
            if (!$parent)
                {  return undef;  };

            push @parents, $parent;

            $self->TryToSkipWhitespace(\$index, \$lineNumber);

            if ($tokens->[$index] ne ',')
                {  last;  }
            else
                {
                $index++;
                $self->TryToSkipWhitespace(\$index, \$lineNumber);
                };
            };
        };

    if ($tokens->[$index] ne '{')
        {  return undef;  };

    $index++;


    # If we made it this far, we have a valid class declaration.

    my $topicType;

    if ($type eq 'interface')
        {  $topicType = ::TOPIC_INTERFACE();  }
    else
        {  $topicType = ::TOPIC_CLASS();  };

    $className =~ s/^_global.//;

    my $autoTopic = NaturalDocs::Parser::ParsedTopic->New($topicType, $className,
                                                                                         undef, $self->CurrentUsing(),
                                                                                         undef,
                                                                                         undef, undef, $$lineNumberRef);

    $self->AddAutoTopic($autoTopic);
    NaturalDocs::Parser->OnClass($autoTopic->Package());

    foreach my $parent (@parents)
        {
        NaturalDocs::Parser->OnClassParent($autoTopic->Package(), NaturalDocs::SymbolString->FromText($parent),
                                                               undef, $self->CurrentUsing(), ::RESOLVE_ABSOLUTE());
        };

    $self->StartScope('}', $lineNumber, $autoTopic->Package());

    $$indexRef = $index;
    $$lineNumberRef = $lineNumber;

    return 1;
    };


#
#   Function: TryToGetFunction
#
#   Determines if the position is on a function declaration, and if so, generates a topic for it, skips it, and returns true.
#
#   Supported Syntaxes:
#
#       - Functions
#       - Constructors
#       - Properties
#       - Functions with _global
#       - Functions with namespaces
#
sub TryToGetFunction #(indexRef, lineNumberRef)
    {
    my ($self, $indexRef, $lineNumberRef) = @_;
    my $tokens = $self->Tokens();

    my $index = $$indexRef;
    my $lineNumber = $$lineNumberRef;

    my $startIndex = $index;
    my $startLine = $lineNumber;

    my @modifiers;
    my $namespace;

    while ($tokens->[$index] =~ /^[a-z]/i)
        {
        if ($tokens->[$index] eq 'function')
            {  last;  }

        elsif (exists $memberModifiers{lc($tokens->[$index])})
            {
            push @modifiers, lc($tokens->[$index]);
            $index++;

            $self->TryToSkipWhitespace(\$index, \$lineNumber);
            }

        elsif (!$namespace)
            {
            do
                {
                $namespace .= $tokens->[$index];
                $index++;
                }
            while ($tokens->[$index] =~ /^[a-z0-9_]/i);

            $self->TryToSkipWhitespace(\$index, \$lineNumber);
            }

        else
            {  last;  };
        };

    if ($tokens->[$index] ne 'function')
        {  return undef;  };
    $index++;

    $self->TryToSkipWhitespace(\$index, \$lineNumber);

    my $type;

    if ($tokens->[$index] eq 'get' || $tokens->[$index] eq 'set')
        {
        # This can either be a property ("function get Something()") or a function name ("function get()").

        my $nextIndex = $index;
        my $nextLineNumber = $lineNumber;

        $nextIndex++;
        $self->TryToSkipWhitespace(\$nextIndex, \$nextLineNumber);

        if ($tokens->[$nextIndex] eq '(')
            {
            $type = ::TOPIC_FUNCTION();
            # Ignore the movement and let the code ahead pick it up as the name.
            }
        else
            {
            $type = ::TOPIC_PROPERTY();
            $index = $nextIndex;
            $lineNumber = $nextLineNumber;
            };
        }
    else
        {  $type = ::TOPIC_FUNCTION();  };

    my $name = $self->TryToGetIdentifier(\$index, \$lineNumber);
    if (!$name)
        {  return undef;  };

    $self->TryToSkipWhitespace(\$index, \$lineNumber);

    if ($tokens->[$index] ne '(')
        {  return undef;  };

    $index++;
    $self->GenericSkipUntilAfter(\$index, \$lineNumber, ')');

    $self->TryToSkipWhitespace(\$index, \$lineNumber);

    if ($tokens->[$index] eq ':')
        {
        $index++;

        $self->TryToSkipWhitespace(\$index, \$lineNumber);

        $self->TryToGetIdentifier(\$index, \$lineNumber, 1);

        $self->TryToSkipWhitespace(\$index, \$lineNumber);
        };


    my $prototype = $self->NormalizePrototype( $self->CreateString($startIndex, $index) );

    if ($tokens->[$index] eq '{')
        {  $self->GenericSkip(\$index, \$lineNumber);  }
    elsif (!exists $declarationEnders{$tokens->[$index]})
        {  return undef;  };


    my $scope = $self->CurrentScope();

    if ($name =~ s/^_global.//)
        {  $scope = undef;  };
    if ($namespace)
        {  $scope = NaturalDocs::SymbolString->Join($scope, $namespace);  };

    $self->AddAutoTopic(NaturalDocs::Parser::ParsedTopic->New($type, $name,
                                                                                              $scope, $self->CurrentUsing(),
                                                                                              $prototype,
                                                                                              undef, undef, $startLine));


    # We succeeded if we got this far.

    $$indexRef = $index;
    $$lineNumberRef = $lineNumber;

    return 1;
    };


#
#   Function: TryToGetVariable
#
#   Determines if the position is on a variable declaration statement, and if so, generates a topic for each variable, skips the
#   statement, and returns true.
#
#   Supported Syntaxes:
#
#       - Variables
#       - Variables with _global
#       - Variables with type * (untyped)
#       - Constants
#       - Variables and constants with namespaces
#
sub TryToGetVariable #(indexRef, lineNumberRef)
    {
    my ($self, $indexRef, $lineNumberRef) = @_;
    my $tokens = $self->Tokens();

    my $index = $$indexRef;
    my $lineNumber = $$lineNumberRef;

    my $startIndex = $index;
    my $startLine = $lineNumber;

    my @modifiers;
    my $namespace;

    while ($tokens->[$index] =~ /^[a-z]/i)
        {
        if ($tokens->[$index] eq 'var' || $tokens->[$index] eq 'const')
            {  last;  }

        elsif (exists $memberModifiers{lc($tokens->[$index])})
            {
            push @modifiers, lc($tokens->[$index]);
            $index++;

            $self->TryToSkipWhitespace(\$index, \$lineNumber);
            }

        elsif (!$namespace)
            {
            do
                {
                $namespace .= $tokens->[$index];
                $index++;
                }
            while ($tokens->[$index] =~ /^[a-z0-9_]/i);

            $self->TryToSkipWhitespace(\$index, \$lineNumber);
            }

        else
            {  last;  };
        };

    my $type;

    if ($tokens->[$index] eq 'var')
        {  $type = ::TOPIC_VARIABLE();  }
    elsif ($tokens->[$index] eq 'const')
        {  $type = ::TOPIC_CONSTANT();  }
    else
        {  return undef;  };
    $index++;

    $self->TryToSkipWhitespace(\$index, \$lineNumber);

    my $endTypeIndex = $index;
    my @names;
    my @types;

    for (;;)
        {
        my $name = $self->TryToGetIdentifier(\$index, \$lineNumber);
        if (!$name)
            {  return undef;  };

        $self->TryToSkipWhitespace(\$index, \$lineNumber);

        my $type;

        if ($tokens->[$index] eq ':')
            {
            $index++;
            $self->TryToSkipWhitespace(\$index, \$lineNumber);

            $type = ': ' . $self->TryToGetIdentifier(\$index, \$lineNumber, 1);

            $self->TryToSkipWhitespace(\$index, \$lineNumber);
            };

        if ($tokens->[$index] eq '=')
            {
            do
                {
                $self->GenericSkip(\$index, \$lineNumber);
                }
            while ($tokens->[$index] ne ',' && !exists $declarationEnders{$tokens->[$index]} && $index < scalar @$tokens);
            };

        push @names, $name;
        push @types, $type;

        if ($tokens->[$index] eq ',')
            {
            $index++;
            $self->TryToSkipWhitespace(\$index, \$lineNumber);
            }
        elsif (exists $declarationEnders{$tokens->[$index]})
            {  last;  }
        else
            {  return undef;  };
        };


    # We succeeded if we got this far.

    my $prototypePrefix = $self->CreateString($startIndex, $endTypeIndex);

    for (my $i = 0; $i < scalar @names; $i++)
        {
        my $prototype = $self->NormalizePrototype( $prototypePrefix . ' ' . $names[$i] . $types[$i]);
        my $scope = $self->CurrentScope();

        if ($names[$i] =~ s/^_global.//)
            {  $scope = undef;  };
        if ($namespace)
            {  $scope = NaturalDocs::SymbolString->Join($scope, $namespace);  };

        $self->AddAutoTopic(NaturalDocs::Parser::ParsedTopic->New($type, $names[$i],
                                                                                                  $scope, $self->CurrentUsing(),
                                                                                                  $prototype,
                                                                                                  undef, undef, $startLine));
        };

    $$indexRef = $index;
    $$lineNumberRef = $lineNumber;

    return 1;
    };



################################################################################
# Group: Low Level Parsing Functions


#
#   Function: GenericSkip
#
#   Advances the position one place through general code.
#
#   - If the position is on a string, it will skip it completely.
#   - If the position is on an opening symbol, it will skip until the past the closing symbol.
#   - If the position is on whitespace (including comments), it will skip it completely.
#   - Otherwise it skips one token.
#
#   Parameters:
#
#       indexRef - A reference to the current index.
#       lineNumberRef - A reference to the current line number.
#
sub GenericSkip #(indexRef, lineNumberRef)
    {
    my ($self, $indexRef, $lineNumberRef) = @_;
    my $tokens = $self->Tokens();

    # We can ignore the scope stack because we're just skipping everything without parsing, and we need recursion anyway.
    if ($tokens->[$$indexRef] eq '{')
        {
        $$indexRef++;
        $self->GenericSkipUntilAfter($indexRef, $lineNumberRef, '}');
        }
    elsif ($tokens->[$$indexRef] eq '(')
        {
        $$indexRef++;
        $self->GenericSkipUntilAfter($indexRef, $lineNumberRef, ')');
        }
    elsif ($tokens->[$$indexRef] eq '[')
        {
        $$indexRef++;
        $self->GenericSkipUntilAfter($indexRef, $lineNumberRef, ']');
        }

    elsif ($self->TryToSkipWhitespace($indexRef, $lineNumberRef) ||
            $self->TryToSkipString($indexRef, $lineNumberRef) ||
            $self->TryToSkipRegExp($indexRef, $lineNumberRef) ||
            $self->TryToSkipXML($indexRef, $lineNumberRef) )
        {
        }

    else
        {  $$indexRef++;  };
    };


#
#   Function: GenericSkipUntilAfter
#
#   Advances the position via <GenericSkip()> until a specific token is reached and passed.
#
sub GenericSkipUntilAfter #(indexRef, lineNumberRef, token)
    {
    my ($self, $indexRef, $lineNumberRef, $token) = @_;
    my $tokens = $self->Tokens();

    while ($$indexRef < scalar @$tokens && $tokens->[$$indexRef] ne $token)
        {  $self->GenericSkip($indexRef, $lineNumberRef);  };

    if ($tokens->[$$indexRef] eq "\n")
        {  $$lineNumberRef++;  };
    $$indexRef++;
    };


#
#   Function: IndiscriminateSkipUntilAfterSequence
#
#   Advances the position indiscriminately until a specific token sequence is reached and passed.
#
sub IndiscriminateSkipUntilAfterSequence #(indexRef, lineNumberRef, token, token, ...)
    {
    my ($self, $indexRef, $lineNumberRef, @sequence) = @_;
    my $tokens = $self->Tokens();

    while ($$indexRef < scalar @$tokens && !$self->IsAtSequence($$indexRef, @sequence))
        {
        if ($tokens->[$$indexRef] eq "\n")
            {  $$lineNumberRef++;  };
        $$indexRef++;
        };

    if ($self->IsAtSequence($$indexRef, @sequence))
        {
        $$indexRef += scalar @sequence;
        foreach my $token (@sequence)
            {
            if ($token eq "\n")
                {  $$lineNumberRef++;  };
            };
        };
    };


#
#   Function: SkipToNextStatement
#
#   Advances the position via <GenericSkip()> until the next statement, which is defined as anything in <declarationEnders> not
#   appearing in brackets or strings.  It will always advance at least one token.
#
sub SkipToNextStatement #(indexRef, lineNumberRef)
    {
    my ($self, $indexRef, $lineNumberRef) = @_;
    my $tokens = $self->Tokens();

    if ($tokens->[$$indexRef] eq ';')
        {  $$indexRef++;  }

    else
        {
        do
            {
  