<!DOCTYPE html>
<html>
<head>
	<title>CRUD operation on Mongodb in PHP using mongodb library</title>
	<link rel="stylesheet" href="../css/bootstrap.min.css">
	<script src="../js/jquery-3.2.1.min.js"></script>
</head>
<body>
	<div class="container">
		<h2 class="text-center" style="margin-top: 5px; padding-top: 0;">CRUD operation on Mongodb in PHP using mongodb library</h2>
		<h3 class="text-center" style="margin-top: 5px; padding-top: 0;">Part 6: Delete selected article document from MongoDB</h3>
		<hr>
		<div class="text-center">
			<?php 
				require_once "vendor/autoload.php";
				$client 	= new MongoDB\Client;
				$dataBase 	= $client->selectDatabase('blog');
				$collection = $dataBase->selectCollection('articles');
				if(isset($_POST['create'])) {
					$data 		= [
						'title' 		=> $_POST['title'],
						'description' 	=> $_POST['description'],
						'author' 		=> $_POST['author'],
						'createdOn' 	=> new MongoDB\BSON\UTCDateTime
					];

					if($_FILES['file']) {
						if(move_uploaded_file($_FILES['file']['tmp_name'], 'upload/'.$_FILES['file']['name'])) {
							$data['fileName'] = $_FILES['file']['name'];
						} else {
							echo "Failed to upload file.";
						}
					}

					$result = $collection->insertOne($data);
					if($result->getInsertedCount()>0) {
						echo "Article is created..";
					} else {
						echo "Failed to create Article";
					}
				}

				if(isset($_POST['update'])) {
					
					$filter		= ['_id' => new MongoDB\BSON\ObjectId($_POST['aid'])];

					$data 		= [
						'title' 		=> $_POST['title'],
						'description' 	=> $_POST['description'],
						'author' 		=> $_POST['author']
					];

					$result = $collection->updateOne($filter, ['$set' => $data]);

					if($result->getModifiedCount()>0) {
						echo "Article is updated..";
					} else {
						echo "Failed to update Article";
					}
				}

				if(isset($_GET['action']) && $_GET['action'] == 'delete') {
					
					$filter		= ['_id' => new MongoDB\BSON\ObjectId($_GET['aid'])];

					$article = $collection->findOne($filter);
					if(!$article) {
						echo "Article not found.";
					}

					$fileName = 'upload/'.$article['fileName'];
					if(file_exists($fileName)) {
						if(!unlink($fileName)) {
							echo "Failed to delete file."; exit;
						}
					}

					$result = $collection->deleteOne($filter);

					if($result->getDeletedCount()>0) {
						echo "Article is deleted..";
					} else {
						echo "Failed to delete Article";
					}

					
				}

			?>
		</div>
		<div class="row">
		    <div class="col-md-4">
			    <form class="form-horizontal" method="post" action="" enctype="multipart/form-data">
					<fieldset>
						<!-- Form Name -->
						<legend style="margin-top: 5px; padding-top: 0;">Article Details</legend>

						<!-- Text input-->
						<div class="form-group">
						  <label class="col-md-12" for="title">Title</label>  
						  <div class="col-md-12">
						  <input id="title" name="title" type="text" placeholder="" class="form-control input-md">
						  </div>
						</div>

						<!-- Text Area-->
						<div class="form-group">
						  <label class="col-md-12" for="description">Description</label>  
						  <div class="col-md-12">
						  <textarea id="description" name="description" placeholder="" class="form-control" rows="6"></textarea>
						  </div>
						</div>

						<!-- Text input-->
						<div class="form-group">
						  <label class="col-md-12" for="author">Author</label>  
						  <div class="col-md-12">
						  <input id="author" name="author" type="text" placeholder="" class="form-control input-md">
						  </div>
						</div>

						<!-- File input-->
						<div class="form-group" id="fileInput">
						  <label class="col-md-12" for="file">Select Image</label>  
						  <div class="col-md-12">
						  <input id="file" name="file" type="file" placeholder="" class="form-control input-md">
						  </div>
						</div>

						<!-- Hidden article id -->
						<input type="hidden" name="aid" id="aid">

						<button id="create" name="create" class="btn btn-primary">Create Article</button>
						<button id="update" style="display: none;" name="update" class="btn btn-primary">Update Article</button>

					</fieldset>
				</form>
		    </div>
		    <div class="col-md-8">
		    	<!-- Show Articles -->
		    	<?php 
		    		$articles = $collection->find();
		    		foreach ($articles as $key => $article) {
		    			$UTCDateTime 	= new MongoDB\BSON\UTCDateTime((string)$article['createdOn']);
		    			$DateTime 		= $UTCDateTime->toDateTime();

		    			$data = json_encode( [
							'id' 			=> (string) $article['_id'],
							'title' 		=> $article['title'],
							'description' 	=> $article['description'],
							'author' 		=> $article['author']
						], true);

		    			echo '<div class="rows">
								<div class="col-md-12">'.$DateTime->format('d/m/Y H:i:s').'</div>
								<div class="rows">
									<div class="col-md-3"><img src="upload/'.$article['fileName'].'" width="180"></div>
									<div class="col-md-8">
										<strong>'.$article['title'].'</strong>
										<p>'.$article['description'].'</p>
										<p class="text-right">'.$article['author'].'</p>
									</div>';
						echo	"<div class='col-md-1'>
									<a href='javascript:updateArticle($data)'>Edit</a><br><br>
									<a href='index.php?action=delete&aid=".$article['_id']."'>Delete</a>
									</div>
								</div>
							</div>";
		    		}
		    	?>
		    </div>
		</div>
	</div>
</body>
</html>
<script type="text/javascript">
	function updateArticle(article) {
		console.log(article);
		$('#aid').val(article.id);
		$('#title').val(article.title);
		$('#description').val(article.description);
		$('#author').val(article.author);

		$('#create').hide();
		$('#fileInput').hide();
		$('#update').show();
	}
</script>