<?php

include("required.php");

class xmlgetfeature
{

	var $output;

	var $service_authentication="";

	function xmlgetfeature($data)
	{
		$separator = (parse_url($data['url'], PHP_URL_QUERY) == NULL) ? '?' : '&';

		$cql=$data["cql_filter"];

		$url=$data['url']."wfs/wfs?version=1.0.0&service=WFS&request=GetFeature&typename=".$data['TypeName']."&CQL_FILTER=".urlencode($cql);

		$this->output=$this->fetchNvalidateGML($url,$data);

	}


	function fetchNvalidateGML($url,$data)
	{

		$gml=file_get_contents($url);

		$dom_xml=new DOMDocument();

		$dom_xml->loadXML($gml);

		$countFeatureMember=$dom_xml->getElementsByTagName("featureMember")->length;

		if ($countFeatureMember>0)
		{

			return $this->fetchAttributes($dom_xml,$data);
		}

		return;
	}


	function fetchAttributes($dom_xml,$data)
	{
		$countFeatureMember=$dom_xml->getElementsByTagName("featureMember")->length;

		if ($countFeatureMember>0)
		{

			$childs=$dom_xml->getElementsByTagName("featureMember");

			$totalRecords=$childs->length;

			$i=0;

			$output.="<ROOT totalRecords='".$totalRecords."'>";

			foreach($childs as $key=>$value)
			{

				$output.="<RECORD>";

				//$authentication=str_replace("&","&amp;",$this->service_authentication);

				//$output.="<authentication>".$authentication."</authentication>";

				//$output.="<serviceURL>".$data['url']."</serviceURL>";

				//$output.="<basename>".$data['layer_basename']."</basename>";

				$childNodes=$value->childNodes;

				foreach($childNodes as $k_child=>$v_child)
				{
					$v_childnodes=$v_child->childNodes;

					$featureId=(string)$v_child->getAttribute("fid");

					$output.="<featureid>".$featureId."</featureid>";

					foreach($v_childnodes as $att_k_child=>$att_v_child)
					{

						if ((string)$att_v_child->localName!=$data['geom_field'])
						{
							$output.="<".(string)$att_v_child->localName.">".htmlspecialchars((string)$att_v_child->nodeValue)."</".(string)$att_v_child->localName.">";
						}

					}

				}

				$output.="</RECORD>";

				$i++;
			}

			$output.="</ROOT>";

		}

		return $output;
	}

}

header('Content-type: application/xml');

$xml=new xmlgetfeature($_REQUEST);

print_r($xml->output);

?>

