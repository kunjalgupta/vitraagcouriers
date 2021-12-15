<?php
return [
    'GET_USER_TABLE_DETAILS' => 'select u.id,u.code_number,u.role,bd.firm_name,u.state,u.state_name,office.area,office.mobile from users u left join address_details office on u.office_address_id = office.id left join business_details bd on u.business_dtls_id = bd.id where u.active_flag = 1',
    'GET_FROM_USER_ID' => ' and u.id=?',
    'GET_USER_ROLE' => ' and u.role=?',
    'GET_RATE_FROM_PINCODE' => 'select rate_type as rate from pincodes where pincode=? and state=?',
    'GET_AREA_FROM_PINCODE' => 'select area from pincodes where pincode=? and state=?',
    'COURIER_REPORT' => 'select c.adding_user_id,c.id as c_id,bd.firm_name,c.awb_number,c.courier_type,c.item,c.weight,c.quantity,c.total_amount,c.pdf_url,u.code_number,u.role,u.name,r.destination,c.created_at,t.status,t.id as track_id from couriers c left join users u on c.adding_user_id = u.id left join receivers r on c.receiver_id=r.id left join business_details as bd on bd.id = u.business_dtls_id left join (SELECT * FROM courier_tracking WHERE id IN (SELECT MAX(id) FROM courier_tracking GROUP BY courier_id) ) as t on  c.id = t.courier_id where 1=1',
    'COURIER_REPORT_ADDING_USER' => ' and c.adding_user_id IN (?)',
    'COURIER_REPORT_DATE' => ' and date(c.created_at) between ? and ?',
    'COURIER_REPORT_PINCODE' => ' and c.pincode=?',
    'COURIER_REPORT_AWB' => ' and c.awb_number=?',
    'USER_LIST' => 'select id,code_number from users where role=?',
    'TRACK_COURIER' => 'select c.awb_number,s.origin,r.destination,c.pdf_url as invoice,s.name as sender_name,s.mobile as sender_mobile,s.address as sender_address,r.name as receiver_name,r.mobile as receiver_mobile,r.address as receiver_address from couriers c left join senders s on  c.sender_id=s.id left join receivers r on c.receiver_id=r.id where c.awb_number=?',
    'COURIER_SALES_COUNT' => 'select year(created_at) as year,month(created_at) as month,count(id) as courier_sales_count from couriers where 1=1 and deleted_at is null and year(created_at)=? group by year(created_at),month(created_at) order by year(created_at),month(created_at)',
    'USER_COUNT' => 'select count(id) as count from users where active_flag=1 and role=?',
    'TOTAL_COURIER_COUNT' => 'select count(id) as count from couriers',
    'COURIER_REPORT_VIEW'=>'select c.awb_number,c.courier_type,c.item,c.weight,c.quantity,c.total_amount,c.created_at as create_date,s.origin,s.name as sender_name,s.mobile as sender_mobile,s.address as sender_address,s.pincode as sender_pincode,r.destination,r.name as receiver_name,r.mobile as receiver_mobile,r.address as receiver_address,r.pincode as receiver_pincode from couriers c left join receivers r on c.receiver_id = r.id left join senders s on c.sender_id = s.id  where c.awb_number=?'
];
