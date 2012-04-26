_server_tools()
{
    local cur prev opts cmd
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    prev="${COMP_WORDS[COMP_CWORD-1]}"
    cmd="${COMP_WORDS[0]}"
    PHP='$ret = shell_exec($argv[1]);

$ret = preg_replace("/^.*Available commands:\n/s", "", $ret);
$ret = explode("\n", $ret);

$comps = array();
foreach ($ret as $line) {
    if (preg_match("@^  ([^ ]+) @", $line, $m)) {
        $comps[] = $m[1];
    }
}

echo implode("\n", $comps);
'
    possible=$($(which php) -r "$PHP" $COMP_WORDS);
    COMPREPLY=( $(compgen -W "${possible}" -- ${cur}) )
    return 0
}
complete -o default -F _server_tools st
COMP_WORDBREAKS=${COMP_WORDBREAKS//:}
