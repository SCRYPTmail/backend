<?php
/**
 * Author: Sergei Krutov
 * Date: 11/2/15
 * For: SCRYPTmail.com.
 * Version: RC 0.99
 */
class MongoDBConnection extends CApplicationComponent {

	public $connectionString;

	public $options;

	public $db;

	public $autoConnect = true;

	/**
	 * The Mongo Connection instance
	 * @var Mongo MongoClient
	 */
	private $_mongo;

	/**
	 * The database instance
	 * @var MongoDB
	 */
	private $_db;

	private $_collection;
	private $connectOptions;
	private $sslOptions;


	/**
	 * The init function
	 * We also connect here
	 * @see yii/framework/base/CApplicationComponent::init()
	 */
	public function init()
	{
		parent::init();
		if($this->options['db']){
			$this->db=$this->options['db'];
			if($this->options['ssl']){
				$this->connectOptions['ssl']=$this->options['ssl'];
				$this->sslOptions=$this->options['sslOptions'];
			}
			$this->connectOptions['db']=$this->options['db'];
			if(isset($this->options['slaveOk'])){
				$this->connectOptions['slaveOkay']=$this->options['slaveOk'];
			}

			if(isset($this->options['replicaSet'])){
				$this->connectOptions['replicaSet']=$this->options['replicaSet'];
			}

			$this->connectOptions['w']=$this->options['writeConcerns'];
			$this->connectOptions['wTimeoutMS']=$this->options['wTimeoutMS'];
		}

		if($this->autoConnect){
			$this->connect();
		}
	}

	/**
	 * Connects to our database
	 */
	public function connect()
	{
		if(!extension_loaded('mongo')){
			throw new EMongoException(
				yii::t(
					'yii',
					'We could not find the MongoDB extension ( http://php.net/manual/en/mongo.installation.php ), please install it'
				)
			);
		}
try {
		$this->_mongo = new MongoClient($this->connectionString, $this->connectOptions,$this->sslOptions);
		$dbname=$this->db;
		$this->_db = $this->_mongo->$dbname;

		//$this->_db->setWriteConcern($this->options['writeConcerns'], $this->options['wTimeoutMS']);
} catch (Exception $e) {

	throw new EMongoException(
		yii::t(
			'yii',
			'We could not find the MongoDB extension ( http://php.net/manual/en/mongo.installation.php ), please install it'
		)
	);
}

	}

	/**
	 * Gets the connection object
	 * Use this to access the Mongo/MongoClient instance within the extension
	 * @return Mongo MongoClient
	 */
	public function getConnection()
	{
		if(empty($this->_mongo)){
			$this->connect();
		}
		return $this->_mongo;
	}

	public function setCollection($name)
	{
		return  $this->_db->$name;
	}

	public function findOne($collectionName,$data,$selectFields=array())
	{

		if($reference = $this->setCollection($collectionName)->findOne($data,$selectFields))
		{
			$result=$reference;
			$result['_id']=(string)$reference['_id'];
		}

		return isset($result)?$result:null;

	}


	public function findAll($collectionName,$data,$selectFields=array(),$limit=null)
	{

		if(empty($limit)){
			$reference = $this->setCollection($collectionName)->find($data,$selectFields);
		}else{
			$reference = $this->setCollection($collectionName)->find($data,$selectFields)->limit($limit);
		}


		foreach ($reference as $i=>$doc)
		{
			$result[$i]=$doc;
			$result[$i]['_id']=$i;
		}


		return isset($result)?$result:null;

	}

	public function findById($collectionName,$id,$selectFields=array())
	{
		$query = array(
			'_id' => new MongoId($id)
		);
		$reference=$this->setCollection($collectionName)->find($query,$selectFields);

		foreach ($reference as $i=>$doc){
			$result=$doc;
			$result['_id']=$i;
		}


		return isset($result)?$result:null;
	}

	public function findByUserIdNew($collectionName,$userId,$selectFields=array())
	{
		$query = array(
			'userId' => $userId
		);

		$reference = $this->setCollection($collectionName)->find($query,$selectFields);

		foreach ($reference as $doc)
			$result[]=$doc;

		return isset($result)?$result:null;

	}

	public function findByManyIds($collectionName,$arrayOfIds,$selectFields=null)
	{
		$query = array(
			'_id' =>array('$in'=>$arrayOfIds)
		);

		$reference = $this->setCollection($collectionName)->find($query,$selectFields);

		foreach ($reference as $i=>$doc){
			$result[$i]=$doc;
			$result[$i]['_id']=$i;

		}

		return isset($result)?array_values($result):null;

	}


	public function generateSlots($collectionName,$userId,$slotAmount) //pregenerate for email insert
	{
		if($slotAmount>0 && isset($userId)){

			for($i=0;$i<$slotAmount;$i++)
				$query[]=array("userId" => (int)$userId, 'removeIn'=>new MongoDate(strtotime('now')));

			$reference = $this->setCollection($collectionName)->batchInsert($query);

			foreach ($query as $doc)
				$result[]=(string)$doc['_id'];

		}

		return isset($result)?$result:null;

	}

	public function removeAll($collectionName,$data)
	{
		$reference = $this->setCollection($collectionName)->remove($data);

		return $reference;
		return isset($reference['err'])?false:true;
	}

	public function removeById($collectionName,$id)
	{

		$reference = $this->setCollection($collectionName)->remove(array('_id' => new MongoId($id)));

		if($reference['ok']==1){
			return true;
		}else if(!empty($reference['err'])){
			return false;
		}

	}


	public function insert($collectionName,$dataArray,$optionsArray=array())
	{

		if(is_array($dataArray)){
			//try{
			$reference = $this->setCollection($collectionName)->batchInsert($dataArray,$optionsArray);

			foreach ($dataArray as $doc)
				$result[]=(string)$doc['_id'];

			//} catch (Exception $e) {
			//	return false;
			//}
		}
	//unset($dataArray,$reference);
		return isset($result)?$result:false;

	}

	public function update($collectionName,$dataArray,$criteria,$inc=null,$upsert=false,$multi=false)
	{

		if(is_array($dataArray)){

			$obj=array();

			if(count($dataArray)>0){
				$obj['$set']=$dataArray;
			}

			if(isset($inc)) {
				$obj['$inc'] = $inc;
			}

			$reference=$this->setCollection($collectionName)->update($criteria,$obj,array('upsert'=>$upsert,'multiple'=>$multi));
		}

		if(isset($reference) && $reference['nModified']>=1)
		{
			return true;
		}else
			return false;


	}

	public function unsetField($collectionName,$dataArray,$criteria)
	{

		if(is_array($dataArray)){

			$obj=array(
				'$unset'=>$dataArray
			);
			$reference=$this->setCollection($collectionName)->update($criteria,$obj);
		}

		if(isset($reference) && $reference['nModified']>=1)
		{
			return true;
		}else
			return false;


	}


	public function upsert($collectionName,$dataArray,$criteria)
	{

		if(is_array($dataArray)){

			$reference=$this->setCollection($collectionName)->update($criteria,$dataArray,array('upsert'=>true));
		}

		if(isset($reference) && ($reference['nModified']>=1 || $reference['n']>=1))
		{
			return true;
		}else {
			return false;
		}


	}

	public function rrt()
	{
		function timeToId($ts) {
			// turn it into hex
			$hexTs = dechex($ts);
			// pad it out to 8 chars
			$hexTs = str_pad($hexTs, 8, "0", STR_PAD_LEFT);
			// make an _id from it
			return new MongoId($hexTs."0000000000000000");
		}

		$start = strtotime("2015-05-20 00:00:00");
		$end = strtotime("2016-10-00 00:00:00");

		$reference =$this->setCollection('mailQueue')->find(array('_id' => array('$gt' => timeToId($start), '$lte' => timeToId($end))),array('_id'=>1,'file'=>1))->limit(650);



			//$reference = $this->setCollection('mailQueue')->find()->sort(array("_id" => 1))->limit(20);

		foreach ($reference as $i=>$doc)
		{
			$result[]=$doc;
		}

		return isset($result)?$result:null;

	}


	/**
	 * You should never call this function.
	 * The PHP driver will handle connections automatically, and will
	 * keep this performant for you.
	 */
	public function close()
	{
		if(!empty($this->_mongo)){
			$this->_mongo->close ();
			return true;
		}
		return false;
	}
}