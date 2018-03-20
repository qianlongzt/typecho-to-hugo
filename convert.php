<?php
$db= new mysqli();
$db->connect('localhost','root','password','typecho');

$prefix = "typecho";
$sql=<<<TEXT
select title,text,created,t2.category,t1.tags,slug from {$prefix}_contents c
left join
 (select cid,CONCAT('"',group_concat(m.name SEPARATOR '","'),'"') tags from {$prefix}_metas m,{$prefix}_relationships r where m.mid=r.mid and m.type='tag' group by cid ) t1
 on c.cid=t1.cid
 left join
(select cid,CONCAT('"',GROUP_CONCAT(m.name SEPARATOR '","'),'"') category from {$prefix}_metas m,{$prefix}_relationships r where m.mid=r.mid and m.type='category' group by cid) t2
on c.cid=t2.cid
TEXT;

$db->query("set character set 'utf8'");//读库
$res=$db->query($sql);
if($res){
    if($res->num_rows>0){

        while($r=$res->fetch_object()) {
            $_c=date('Y-m-d H:i:s',$r->created);
            $_t=str_replace('<!--markdown-->','',$r->text);
            $_a = str_replace(array('C#'),'CSharp',$r->tags);
            $_g = str_replace(array('C#'),'CSharp',$r->category);
            $_g = str_replace(array(','),'-',$_g);

            $_tmp = <<<TMP
+++
title= "$r->title"
categories= [$r->category]
tags= [$r->tags]
date= "$_c"
+++

$_t

TMP;
            $file_name=$r->slug;
           file_put_contents(str_replace(array(" ","?","\\","/" ,":" ,"|", "*" ),'-',$file_name).".md",$_tmp);
        }
    }
    $res->free();
}

$db->close();
