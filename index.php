<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        <?php include "index.css" ?>
    </style>
</head>
<body>
    <?php
    $rowError = $colError = $shipError = $attackError = "";
    $rows =
        $cols =
        $numberOfShips =
        $numberOfAttack = null;
    $weapon = null;
    $validated = false;
    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['form_submit'])) {
        if (empty($_POST['rows']) || intval($_POST['rows']) < 3) {
            $rowError = 'Minimum is 3';
        } else {
            $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 3;
        }
        if (empty($_POST['cols']) || intval($_POST['cols']) < 3) {
            $colError = 'Minimum is 3';
        } else {
            $cols =  isset($_POST['cols']) ? intval($_POST['cols']) : 3;
        }
        if (empty($_POST['numShips'])) {
            $shipError = 'Minimum is 1';
        } else {
            if (intval($_POST['numShips']) * 2 > $rows * $cols) {
                $shipError = 'Too many ships for this table';
            } else {
                $numberOfShips = isset($_POST['numShips']) ? intval($_POST['numShips']) : 1;
            }
        }
        if (empty($_POST['numAttack'])) {
            $attackError = 'Minimum is 1';
        } else {
            if ($_POST['numAttack'] > $rows * $cols) {
                $attackError = 'Too many attacks for this table';
            } else {
                $numberOfAttack = isset($_POST['numAttack']) ? intval($_POST['numAttack']) : 1;
            }
        }
        $weapon = $_POST['attackType'];
        if ($rowError == "" && $colError == "" && $shipError == "" && $attackError == "") {
            $validated = true;
        }
    }
    ?>
    <div class="container">
        <div class="form-container" style="text-align: center;">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="rows">Rows: </label>
                <input type="number" name="rows" id="rows" value="" min="1" max="100">
                <span style="color: red;"><?php echo $rowError; ?></span><br><br>
                <label for="cols">Columns: </label>
                <input type="number" name="cols" id="cols" value="" min="1" max="100">
                <span style="color: red;"><?php echo $colError; ?></span><br><br>
                <label for="numShips">Number of ships: </label>
                <input type="number" name="numShips" id="numShips" value="" min="1">
                <span style="color: red;"><?php echo $shipError; ?></span><br><br>
                <label for="numAttack">Number of attacks: </label>
                <input type="number" name="numAttack" id="numAttack" value="" min="1">
                <span style="color: red;"><?php echo $attackError; ?></span><br><br>
                <label for="attackType">Attack type: </label>
                <select name="attackType">
                    <option value="">--Chose an option--</option>
                    <option value="single" selected>Single bomb</option>
                    <option value="rocket">Rocket bomb</option>
                    <option value="hydro">Hydro bomb</option>
                    <option value="napoleon">Napoleon bomb</option>
                </select>
                <button type="submit" name="form_submit">Generate Table</button>
            </form>
        </div>
        <br>
        <div class="table-container">
            <?php
            if ($validated) {

                $start = putShip($rows, $cols, $numberOfShips);
                generateTables($start);
                $hits = [];
                $afterAttack = attack($start, $numberOfAttack, $weapon);
                generateTables($afterAttack);

                foreach ($hits as $ship) {
                    echo "<p><strong>Name:</strong> " . $ship['name'] . '<strong> ===>  Coordinates:</strong>' . implode(', ', $ship['coordinates']) . "</p>";
                }
            } else {
                echo "Welcome to the game";
            }
            function generateTables($table)
            {
                $rows = count((array)$table);
                $cols = count((array)$table[0]);
                echo '<table style="grid-template-columns: repeat($cols, 50px);grid-template-rows: repeat($rows, 50px);">';
                echo '<thead>';
                echo '<th></th>';
                for ($i = 1; $i <= $cols; $i++) {
                    echo "<td>$i</td>";
                }
                echo '</thead>';
                echo '</tbody>';
                foreach ($table as $index => $rows) {
                    echo "<tr>";
                    echo "<td>" . $index + 1 . "</td>";
                    foreach ($rows as $index => $cell) {
                        echo "<td class='{$cell["class"]}'></td>";
                    }
                    echo "</tr>";
                }
                echo '</tbody>';
                echo '</table>';
            }
            function putShip($rows, $cols, $numberOfShips)
            {
                $table = array_fill(0, $rows, array_fill(0, $cols, array_fill_keys(['class', 'id'], null)));
                $count = 1;
                $id = 1;
                while ($count <= $numberOfShips) {
                    $done = false;
                    while (!$done) {
                        $xr = rand(0, $rows - 1);
                        $yc = rand(0, $cols - 1);
                        $shipSize = rand(2, 3);
                        $direction = rand(1, 4);
                        if (canPlace($rows, $cols, $xr, $yc, $shipSize, $direction, $table)) {
                            switch ($direction) {
                                case 1:
                                    for ($i = 0; $i < $shipSize; $i++) {
                                        if ($i == 0) {
                                            $table[$xr][$yc  + $i]['class'] = 'beginning-right';
                                            $table[$xr][$yc  + $i]['id'] = ($id * 10) + $i;
                                        } else if ($i == $shipSize - 1) {
                                            $table[$xr][$yc  + $i]['class'] = 'arrow-right';
                                            $table[$xr][$yc  + $i]['id'] = ($id * 10) + $i;
                                        } else {
                                            $table[$xr][$yc  + $i]['class'] = 'full';
                                            $table[$xr][$yc  + $i]['id'] = ($id * 10) + $i;
                                        }
                                    }
                                    break;
                                case 2:
                                    for ($i = 0; $i < $shipSize; $i++) {
                                        if ($i == 0) {
                                            $table[$xr + $i][$yc]['class'] = 'beginning-down';
                                            $table[$xr + $i][$yc]['id'] = ($id * 10) + $i;
                                        } else if ($i == $shipSize - 1) {
                                            $table[$xr + $i][$yc]['class'] = 'arrow-down';
                                            $table[$xr + $i][$yc]['id'] = ($id * 10) + $i;
                                        } else {
                                            $table[$xr + $i][$yc]['class'] = 'full';
                                            $table[$xr + $i][$yc]['id'] = ($id * 10) + $i;
                                        }
                                    }
                                    break;
                                case 3:
                                    for ($i = 0; $i < $shipSize; $i++) {
                                        if ($i == 0) {
                                            $table[$xr][$yc  - $i]['class'] = 'beginning-left';
                                            $table[$xr][$yc  - $i]['id'] = ($id * 10) + $i;
                                        } else if ($i == $shipSize - 1) {
                                            $table[$xr][$yc  - $i]['class'] = 'arrow-left';
                                            $table[$xr][$yc  - $i]['id'] = ($id * 10) + $i;
                                        } else {
                                            $table[$xr][$yc  - $i]['class'] = 'full';
                                            $table[$xr][$yc  - $i]['id'] = ($id * 10) + $i;
                                        }
                                    }
                                    break;
                                case 4:
                                    for ($i = 0; $i < $shipSize; $i++) {
                                        if ($i == 0) {
                                            $table[$xr - $i][$yc]['class'] = 'beginning-up';
                                            $table[$xr - $i][$yc]['id'] = ($id * 10) + $i;
                                        } else if ($i == $shipSize - 1) {
                                            $table[$xr - $i][$yc]['class'] = 'arrow-up';
                                            $table[$xr - $i][$yc]['id'] = ($id * 10) + $i;
                                        } else {
                                            $table[$xr - $i][$yc]['class'] = 'full';
                                            $table[$xr - $i][$yc]['id'] = ($id * 10) + $i;
                                        }
                                    }
                                    break;
                            }
                            $done = true;
                            $count++;
                            $id++;
                        }
                    }
                }
                return $table;
            }
            function canPlace($rows, $cols, $xr, $yc, $shipSize, $direction, $array)
            {
                switch ($direction) {
                    case 1: // right
                        if ($yc + $shipSize > $cols) return false;
                        for ($i = 0; $i < $shipSize; $i++) {
                            if ($array[$xr][$yc  + $i]['class'] !== null) return false;
                        }
                        break;
                    case 2: // down
                        if ($xr + $shipSize > $rows) return false;
                        for ($i = 0; $i < $shipSize; $i++) {
                            if ($array[$xr + $i][$yc]['class'] !== null) return false;
                        }
                        break;
                    case 3: // left
                        if ($yc - $shipSize < -1) return false;
                        for ($i = 0; $i < $shipSize; $i++) {
                            if ($array[$xr][$yc  - $i]['class'] !== null) return false;
                        }
                        break;
                    case 4: // up
                        if ($xr - $shipSize < -1) return false;
                        for ($i = 0; $i < $shipSize; $i++) {
                            if ($array[$xr - $i][$yc]['class'] !== null) return false;
                        }
                        break;
                }
                return true;
            }
            function attack($table, $numberOfAttack, $weapon)
            {
                $hits = [];
                $count = 0;
                $shipClasses = [
                    'beginning-left',
                    'beginning-right',
                    'beginning-up',
                    'beginning-down',
                    'arrow-left',
                    'arrow-right',
                    'arrow-up',
                    'arrow-down',
                    'full',
                    'shot'
                ];
                $weapons = [
                    [
                        'name' => 'single',
                        'power' => 1,
                        'shape' => 'dot',
                        'coordinates' => null
                    ],
                    [
                        'name' => 'rocket',
                        'power' => 2,
                        'shape' => 'horizontal',
                        'coordinates' => null
                    ],
                    [
                        'name' => 'hydro',
                        'power' => 4,
                        'shape' => 'square',
                        'coordinates' => null
                    ],
                    [
                        'name' => 'napoleon',
                        'power' => 3,
                        'shape' => 'vertical',
                        'coordinates' => null
                    ]
                ];
                while ($count < $numberOfAttack) {
                    $rows = count((array)$table);
                    $cols = count((array)$table[0]);
                    $row = rand(0, $rows - 1);
                    $col = rand(0, $cols - 1);
                    
                    // $hit = $weapons[];
                    // $hit['coordinates'] = [$row, $col];
                    switch ($weapon) {
                        case 'single':
                            if (in_array($table[$row][$col]['class'], $shipClasses)) {
                                $table[$row][$col]['class'] = "shot";
                            } else {
                                $table[$row][$col]['class'] = "single";
                                $count++;
                                array_push($hits, ['name' => $weapon,'coordinates' => [$row, $col]]);
                                break;
                            }
                        case 'rocket':
                            if ($col + 1 <= $cols - 1) {
                                if (in_array($table[$row][$col]['class'], $shipClasses)) {
                                    $table[$row][$col]['class'] = "shot";
                                } else {
                                    $table[$row][$col]['class'] = "rocket";
                                }
                                if (in_array($table[$row][$col + 1]['class'], $shipClasses)) {
                                    $table[$row][$col + 1]['class'] = "shot";
                                } else {
                                    $table[$row][$col + 1]['class'] = "rocket";
                                }
                                $count++;
                                array_push($hits, ['name' => $weapon,'coordinates' => [$row, $col]]);
                                break;
                            }
                            break;
                        case 'hydro':
                            if ($col + 1 <= $cols - 1 && $row + 1 <= $rows - 1) {
                                if (in_array($table[$row][$col]['class'], $shipClasses)) {
                                    $table[$row][$col]['class'] = "shot";
                                } else {
                                    $table[$row][$col]['class'] = "hydro";
                                }
                                if (in_array($table[$row][$col + 1]['class'], $shipClasses)) {
                                    $table[$row][$col + 1]['class'] = "shot";
                                } else {
                                    $table[$row][$col + 1]['class'] = "hydro";
                                }
                                if (in_array($table[$row + 1][$col + 1]['class'], $shipClasses)) {
                                    $table[$row + 1][$col + 1]['class'] = "shot";
                                } else {
                                    $table[$row + 1][$col + 1]['class'] = "hydro";
                                }
                                if (in_array($table[$row + 1][$col]['class'], $shipClasses)) {
                                    $table[$row + 1][$col]['class'] = "shot";
                                } else {
                                    $table[$row + 1][$col]['class'] = "hydro";
                                }
                                $count++;
                                array_push($hits, ['name' => $weapon,'coordinates' => [$row, $col]]);
                                break;
                            }
                            break;
                        case 'napoleon':
                            if ($row + 2 <= $rows - 1) {
                                if (in_array($table[$row][$col]['class'], $shipClasses)) {
                                    $table[$row][$col]['class'] = "shot";
                                } else {
                                    $table[$row][$col]['class'] = "napoleon";
                                }
                                if (in_array($table[$row + 1][$col]['class'], $shipClasses)) {
                                    $table[$row + 1][$col]['class'] = "shot";
                                } else {
                                    $table[$row + 1][$col]['class'] = "napoleon";
                                }
                                if (in_array($table[$row + 2][$col]['class'], $shipClasses)) {
                                    $table[$row + 2][$col]['class'] = "shot";
                                } else {
                                    $table[$row + 2][$col]['class'] = "napoleon";
                                }
                                $count++;
                                array_push($hits, ['name' => $weapon,'coordinates' => [$row, $col]]);
                                break;
                            }
                            break;
                    }
                }
                return $table;
            }
            ?>
        </div>
    </div>
</body>
</html>