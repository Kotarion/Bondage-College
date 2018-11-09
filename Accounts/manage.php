<?php 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("content-type: text/plain; charset=utf-8"); 
?>

<?php

// Returns the character file name
function GetFileName() {
	return 'characters/'.$_GET["account"].'.json';
}

// For any transaction, we validate the login
function ValidLogin(&$data) {
	
	// Checks if the login parameters are valid
	if (isset($_GET["account"]) && isset($_GET["password"]) && ($_GET["account"] != "") && ($_GET["password"] != "")) {
		
		// The character file is named like the account, the password is hashed in the file
		$file = GetFileName();
		if (file_exists($file)) {
			$myfile = fopen($file, "r") or die("Unable to open file!");
			$data = fread($myfile, filesize($file));
			fclose($myfile);
			$arr = json_decode($data);
			if (password_verify($_GET["password"], $arr->Password)) {
				return true;
			} else echo "invalid_password";
		} else echo "account_doesnt_exist";
	} else echo "parameter_error";
	return false;

}

// There needs to be a command first
if (isset($_GET["command"])) {

	// Checks if all the parameters are there to create an account
	if ($_GET["command"] == "account_create")
		if (isset($_GET["account"]) && isset($_GET["password"]) && isset($_GET["character"]) && isset($_GET["email"]) && ($_GET["account"] != "") && ($_GET["password"] != "") && ($_GET["character"] != "")) {
			
			// The character file is named like her, we check if it already exists
			$file = GetFileName();
			if (!file_exists($file)) {
				$arr = new stdClass();
				$arr->AccountName = $_GET["account"];
				$arr->Password = password_hash($_GET["password"], PASSWORD_DEFAULT);
				$arr->Email = password_hash($_GET["email"], PASSWORD_DEFAULT);
				$arr->CharacterName = $_GET["character"];
				$handle = fopen($file, 'w') or die('Cannot open file: '.$file);
				fwrite($handle, json_encode($arr));
				fclose($handle);
				echo "account_created";		
			} else echo "account_already_exist";

		} else echo "parameter_error";

	// Checks if all the parameters are there to log in
	if ($_GET["command"] == "account_log") 
		if (ValidLogin($data))
			echo $data;

	// Add an item to the account inventory
	if ($_GET["command"] == "inventory_add") 
		if (ValidLogin($data))
			if (isset($_GET["name"]) && isset($_GET["group"]) && ($_GET["name"] != "") && ($_GET["group"] != "")) {

				// If the item is already in inventory, we exit
				$arr = json_decode($data);
				if (!isset($arr->Inventory)) $arr->Inventory = [];
				foreach ($arr->Inventory as $item)
					if (($item->Name == $_GET["name"]) && ($item->Group == $_GET["group"]))
						die("already_in_inventory");

				// Create the inventory item and add it
				$inventory = new stdClass();
				$inventory->Name = $_GET["name"];
				$inventory->Group = $_GET["group"];
				array_push($arr->Inventory, $inventory);

				// Overwrite the file
				$file = GetFileName();
				$myfile = fopen($file, "w") or die("Unable to open file!");
				fwrite($myfile, json_encode($arr));
				fclose($myfile);
				echo "inventory_added";
					
			} else echo "parameter_error";

	// Add an item to the account inventory
	if ($_GET["command"] == "appearance_update") 
		if (ValidLogin($data)) {

			// Decodes the data
			$arr = json_decode($data);
			$arr->Appearance = [];
			
			// Fills the appearance array
			$p = 0;
			while (isset($_GET["name".$p]) && isset($_GET["group".$p]) && isset($_GET["family".$p]) && isset($_GET["color".$p]) && ($_GET["name".$p] != "") && ($_GET["group".$p] != "") && ($_GET["family".$p] != "") && ($_GET["color".$p] != "")) {

				// Adds the appearance in the array
				$appearance = new stdClass();
				$appearance->Name = $_GET["name".$p];
				$appearance->Group = $_GET["group".$p];
				$appearance->Family = $_GET["family".$p];				
				$appearance->Color = str_replace("|", "#", $_GET["color".$p]);
				array_push($arr->Appearance, $appearance);
				$p++;
			
			}

			// Overwrite the file
			$file = GetFileName();
			$myfile = fopen($file, "w") or die("Unable to open file!");
			fwrite($myfile, json_encode($arr));
			fclose($myfile);
			echo "appearance_updated";
			
		}
		
} else echo "no_command_error";

?>