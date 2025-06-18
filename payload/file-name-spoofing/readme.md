exploit file name spoofing trong zip
kịch bản:
	ứng dụng sử dụng zip archive để kiểm tra file upload và các file entry của zip file
	tuy nhiên sau đó lại sử dụng '7zz l <filename.zip>' để unzip file
	cơ chế này chỉ unzip file dựa trên file header chứ không sử dụng file entries -> pwned

Tấn công:
	Tạo file shell.php
	Thay đổi mimetype của file này để vượt qua cơ chế bảo mật kiểm tra mime type
	Zip file như bình thường
	Sử dụng HxD để chỉnh sửa trên file trong central directory thành ext được cho phép
	Upload lên server
