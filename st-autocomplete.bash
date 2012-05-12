_server_tools()
{
    local cur opts
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    opts=$($(which st) list --raw | awk '{ print $1 }')
    COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
    return 0
}
complete -o default -F _server_tools st
COMP_WORDBREAKS=${COMP_WORDBREAKS//:}
