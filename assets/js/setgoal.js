function taskDelete() {
    $.post('setgoal.php',
        { TaskDeleteFlag: 1 })
}